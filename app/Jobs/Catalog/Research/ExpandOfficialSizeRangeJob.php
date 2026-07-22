<?php

namespace App\Jobs\Catalog\Research;

use App\Enums\Catalog\Research\ResearchJobStatusEnum;
use App\Enums\Catalog\Research\ResearchJobTypeEnum;
use App\Models\Catalog\Research\ProductModel;
use App\Models\Catalog\Research\ResearchJob;
use App\Services\Catalog\Research\DeepSeek\DeepSeekCatalogResearchService;
use App\Services\Catalog\Research\DeepSeek\Dto\ResearchRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Asks which sizes a manufacturer OFFICIALLY publishes for one specific model.
 *
 * This is the safe multiplier. A model recorded once as "available 1/2 inch to
 * 4 inch" becomes several documented variants only when the manufacturer
 * itself enumerates those sizes — the model is never allowed to expand a range
 * on its own, which would be invention wearing a range's clothes.
 *
 * Targets models that currently have few variants, since those are the ones
 * whose published range has not been read yet.
 */
class ExpandOfficialSizeRangeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 1;

    public function __construct(private readonly int $productModelId) {}

    public function handle(DeepSeekCatalogResearchService $service): void
    {
        $model = ProductModel::with(['manufacturer', 'series'])->find($this->productModelId);

        if (! $model || ! $model->manufacturer) {
            return;
        }

        $modelNumber = (string) ($model->model_number ?? '');
        if ($modelNumber === '') {
            return; // nothing identifiable to ask about
        }

        $job = ResearchJob::create([
            'uuid'              => (string) Str::uuid(),
            'product_family_id' => $model->product_family_id,
            'manufacturer_id'   => $model->manufacturer_id,
            'job_type'          => ResearchJobTypeEnum::SizeRangeExpansion,
            'provider'          => config('catalog_research.provider', 'deepseek'),
            'model_name'        => config('catalog_research.deepseek.model'),
            'research_query'    => "{$model->manufacturer->name} {$modelNumber} — published sizes",
            'input_payload'     => [
                'manufacturer' => $model->manufacturer->name,
                'model_number' => $modelNumber,
                'series_name'  => $model->series?->series_name,
                'source_url'   => $model->series?->official_page_url,
            ],
            'status'            => ResearchJobStatusEnum::Processing,
            'priority'          => 6,
            'attempts'          => 0,
            'max_attempts'      => 1,
            'started_at'        => now(),
        ]);

        $request = ResearchRequest::make(
            type: ResearchJobTypeEnum::SizeRangeExpansion,
            familyName: (string) ($model->product_name ?? $modelNumber),
            normalizedFamilyName: mb_strtolower($modelNumber),
            context: (array) $job->input_payload,
            manufacturerName: $model->manufacturer->name,
        );

        try {
            $response = $service->runForJob($job, $request);
        } catch (\Throwable $e) {
            Log::warning('Size range expansion failed.', [
                'model'   => $modelNumber,
                'message' => $e->getMessage(),
            ]);

            $job->update([
                'status'        => ResearchJobStatusEnum::Failed,
                'failed_at'     => now(),
                'error_message' => $e->getMessage(),
            ]);

            return;
        }

        if (! $response->valid) {
            return;
        }

        // Persist through the standard pipeline so the source and verification
        // rules apply here exactly as they do to discovery.
        ProcessResearchResultJob::dispatchSync($job->id);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ExpandOfficialSizeRangeJob died.', [
            'product_model_id' => $this->productModelId,
            'message'          => $e->getMessage(),
        ]);
    }
}
