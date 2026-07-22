<?php

namespace Tests\Feature\Catalog\Research;

use App\Enums\Catalog\Research\ResearchJobStatusEnum;
use App\Enums\Catalog\Research\ResearchJobTypeEnum;
use App\Enums\Catalog\Research\ResearchStatusEnum;
use App\Enums\Catalog\Research\VerificationLevelEnum;
use App\Enums\Catalog\Research\VerificationStatusEnum;
use App\Jobs\Catalog\Research\ProcessResearchResultJob;
use App\Jobs\Catalog\Research\ResearchProductFamilyJob;
use App\Models\Catalog\Research\Approval;
use App\Models\Catalog\Research\ProductFamily;
use App\Models\Catalog\Research\ProductVariant;
use App\Models\Catalog\Research\ResearchJob;
use App\Services\Catalog\Research\DeepSeek\Dto\ResearchResponse;
use App\Services\Catalog\Research\DeepSeek\FakeResearchProvider;
use App\Services\Catalog\Research\ResearchPlanService;
use Illuminate\Support\Facades\Queue;

class ResearchPipelineTest extends CatalogResearchTestCase
{
    private function family(array $overrides = []): ProductFamily
    {
        return ProductFamily::create(array_merge([
            'name'            => 'Ball Valve (Brass)',
            'normalized_name' => 'ball valve (brass)',
            'research_status' => ResearchStatusEnum::NotStarted,
            'research_priority' => 5,
            'research_scope'  => 'saudi',
        ], $overrides));
    }

    private function officialVariantResponse(): string
    {
        return json_encode([
            'manufacturer' => ['name' => 'NIBCO', 'official_website' => 'https://www.nibco.com', 'country' => 'US'],
            'series' => [[
                'series_name' => 'KT-585-70-UL',
                'official_page_url' => 'https://www.nibco.com/kt585',
                'models' => [[
                    'model_number' => 'KT-585-70-UL',
                    'body_material' => 'Bronze', 'port_type' => 'Full Port', 'pieces' => 2,
                    'variants' => [[
                        'manufacturer_sku' => 'NL95046',
                        'size' => '1/2 inch', 'connection' => 'Female NPT', 'pressure_rating' => '300 PSI',
                        'approvals' => [
                            ['name' => 'UL Listed', 'code' => 'UL 258', 'scope' => 'Sprinkler Trim'],
                            ['name' => 'UL Listed', 'code' => 'UL 842', 'scope' => 'Flammable Fluids'],
                        ],
                        'standards' => ['MSS SP-110'],
                        'verification_level' => 'exact_manufacturer_sku',
                        'availability_status' => 'current',
                        'sources' => [[
                            'title' => 'NIBCO page', 'url' => 'https://www.nibco.com/kt585',
                            'source_type' => 'official_product_page', 'is_official' => true,
                            'supports_fields' => ['manufacturer_sku', 'pressure_rating'],
                        ]],
                    ]],
                ]],
            ]],
            'unverified_items' => [], 'warnings' => [],
        ]);
    }

    public function test_start_dispatches_first_stage_and_prevents_concurrent_runs(): void
    {
        Queue::fake();
        $family = $this->family();
        $plan   = app(ResearchPlanService::class);

        $job = $plan->start($family);

        $this->assertSame(ResearchJobTypeEnum::DiscoverManufacturers, $job->job_type);
        $this->assertSame(ResearchStatusEnum::Queued, $family->fresh()->research_status);
        Queue::assertPushed(ResearchProductFamilyJob::class);

        // A second start while research is already scheduled/running must fail
        // (guarded either by the family status or the active-job count).
        $this->expectException(\RuntimeException::class);
        $plan->start($family->fresh());
    }

    public function test_full_pipeline_persists_official_variant_as_verified(): void
    {
        // Register the manufacturer's official domain so source matching passes.
        \App\Models\Catalog\Research\Manufacturer::create([
            'name' => 'NIBCO', 'normalized_name' => 'nibco',
            'official_domain' => 'nibco.com',
        ]);

        $family = $this->family();

        // Script the fake provider to return an official variant.
        $fake = app(FakeResearchProvider::class);
        $fake->queueRaw($this->officialVariantResponse());
        $this->app->instance(\App\Services\Catalog\Research\DeepSeek\Contracts\AiResearchProvider::class, $fake);

        // Create + run the discovery job synchronously (no real queue).
        $job = ResearchJob::create([
            'product_family_id' => $family->id,
            'job_type'          => ResearchJobTypeEnum::DiscoverVariants,
            'status'            => ResearchJobStatusEnum::Queued,
        ]);

        app()->call([app(ResearchProductFamilyJob::class, ['researchJobId' => $job->id]), 'handle']);
        // The family job dispatches ProcessResearchResultJob; run it directly.
        app()->call([app(ProcessResearchResultJob::class, ['researchJobId' => $job->id]), 'handle']);

        // One real variant created (no cartesian expansion).
        $this->assertSame(1, ProductVariant::count());
        $variant = ProductVariant::first();
        $this->assertSame('NL95046', $variant->manufacturer_sku);
        $this->assertSame(VerificationLevelEnum::ExactManufacturerSku, $variant->verification_level);
        $this->assertSame(VerificationStatusEnum::Verified, $variant->verification_status);

        // UL 258 and UL 842 persisted as DISTINCT approvals.
        $codes = $variant->approvals()->pluck('approval_code')->all();
        $this->assertContains('UL 258', $codes);
        $this->assertContains('UL 842', $codes);
        $this->assertSame(2, Approval::whereIn('approval_code', ['UL 258', 'UL 842'])->count());

        // Job + family advanced.
        $this->assertSame(ResearchJobStatusEnum::Completed, $job->fresh()->status);
        $this->assertSame(ResearchStatusEnum::Verified, $family->fresh()->research_status);
    }

    public function test_variant_without_official_source_is_not_verified(): void
    {
        $family = $this->family();

        $raw = json_encode([
            'manufacturer' => ['name' => 'Generic Co', 'official_website' => null, 'country' => null],
            'series' => [[
                'series_name' => 'S1',
                'models' => [[
                    'model_number' => 'M1',
                    'variants' => [[
                        'manufacturer_sku' => 'SKU1', 'size' => '1 inch', 'connection' => 'NPT', 'pressure_rating' => '150 PSI',
                        'approvals' => [], 'standards' => [],
                        'verification_level' => 'distributor_only',
                        'availability_status' => 'unknown',
                        'sources' => [[
                            'title' => 'Distributor', 'url' => 'https://some-distributor.example/p/1',
                            'source_type' => 'authorized_distributor', 'is_official' => false, 'supports_fields' => [],
                        ]],
                    ]],
                ]],
            ]],
            'unverified_items' => [], 'warnings' => [],
        ]);

        $fake = app(FakeResearchProvider::class);
        $fake->queueRaw($raw);
        $this->app->instance(\App\Services\Catalog\Research\DeepSeek\Contracts\AiResearchProvider::class, $fake);

        $job = ResearchJob::create([
            'product_family_id' => $family->id,
            'job_type'          => ResearchJobTypeEnum::DiscoverVariants,
            'status'            => ResearchJobStatusEnum::Queued,
        ]);
        app()->call([app(ResearchProductFamilyJob::class, ['researchJobId' => $job->id]), 'handle']);
        app()->call([app(ProcessResearchResultJob::class, ['researchJobId' => $job->id]), 'handle']);

        $variant = ProductVariant::first();
        $this->assertNotNull($variant);
        // Distributor-only → partially verified at best, never "verified".
        $this->assertNotSame(VerificationStatusEnum::Verified, $variant->verification_status);
        $this->assertSame(VerificationLevelEnum::DistributorOnly, $variant->verification_level);
    }

    public function test_pause_cancels_pending_jobs_and_sets_status(): void
    {
        Queue::fake();
        $family = $this->family();
        $plan   = app(ResearchPlanService::class);
        $plan->start($family);

        $plan->pause($family->fresh());

        $this->assertSame(ResearchStatusEnum::Paused, $family->fresh()->research_status);
        $this->assertSame(0, ResearchJob::where('product_family_id', $family->id)
            ->whereIn('status', ['pending', 'queued'])->count());
    }

    public function test_idempotent_persist_does_not_duplicate_variants(): void
    {
        \App\Models\Catalog\Research\Manufacturer::create([
            'name' => 'NIBCO', 'normalized_name' => 'nibco', 'official_domain' => 'nibco.com',
        ]);
        $family = $this->family();

        $persister = app(\App\Services\Catalog\Research\Contracts\ResearchResultPersister::class);
        $job = ResearchJob::create([
            'product_family_id' => $family->id,
            'job_type' => ResearchJobTypeEnum::DiscoverVariants,
            'status' => ResearchJobStatusEnum::Processing,
        ]);
        $response = ResearchResponse::valid(json_decode($this->officialVariantResponse(), true), '');

        $persister->persist($job, $response);
        $persister->persist($job, $response); // run again

        $this->assertSame(1, ProductVariant::count());
    }
}
