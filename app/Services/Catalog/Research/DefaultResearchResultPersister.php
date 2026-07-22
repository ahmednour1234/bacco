<?php

namespace App\Services\Catalog\Research;

use App\Enums\Catalog\Research\SourceTypeEnum;
use App\Models\Catalog\Research\Manufacturer;
use App\Models\Catalog\Research\ProductFamily;
use App\Models\Catalog\Research\ProductModel;
use App\Models\Catalog\Research\ProductSeries;
use App\Models\Catalog\Research\ProductVariant;
use App\Models\Catalog\Research\ResearchJob;
use App\Models\Catalog\Research\SourceDocument;
use App\Repositories\Catalog\Research\LookupRepository;
use App\Services\Catalog\Research\Contracts\ResearchResultPersister;
use App\Services\Catalog\Research\DeepSeek\Dto\ResearchResponse;
use Illuminate\Support\Facades\DB;

/**
 * Persists a validated AI research response into the catalog. Everything runs in
 * a single transaction on the catalog connection and is idempotent via each
 * variant's unique normalized_variant_key, so re-running never duplicates.
 *
 * The anti-hallucination rules are enforced HERE, not trusted from the AI:
 *  - each variant's verification level/status is (re)computed by VerificationService,
 *  - approvals are resolved per (issuing body + code) so UL 258 ≠ UL 842,
 *  - every approval / source-backed field records ProductSourceEvidence,
 *  - a source with no URL or a blacklisted domain is flagged, not trusted,
 *  - no cartesian expansion: only variants actually present in the response are created.
 */
class DefaultResearchResultPersister implements ResearchResultPersister
{
    public function __construct(
        private LookupRepository          $lookups,
        private NormalizationEngine       $engine,
        private VerificationService       $verification,
        private SourceVerificationService $sourceVerifier,
    ) {}

    public function persist(ResearchJob $job, ResearchResponse $response): array
    {
        $family = $job->family;
        if (! $family) {
            return ['accepted' => 0, 'rejected' => 0, 'duplicate' => 0];
        }

        return DB::connection('catalog')->transaction(
            fn () => $this->doPersist($family, $response)
        );
    }

    /** @return array{accepted:int, rejected:int, duplicate:int} */
    private function doPersist(ProductFamily $family, ResearchResponse $response): array
    {
        $accepted = $rejected = $duplicate = 0;

        // A response may carry ONE top-level manufacturer, or the manufacturer
        // may be named per series/variant (the "discover many manufacturers"
        // shape). Resolve a default, but always fall back to per-node names so a
        // missing top-level node never rejects every variant.
        $defaultManufacturer = $this->resolveManufacturer($family, $response->data['manufacturer'] ?? null);

        foreach ($response->series() as $seriesNode) {
            // Manufacturer for this series: series/first-variant name, else default.
            $manufacturer = $this->manufacturerForNode($family, $seriesNode, $defaultManufacturer);

            $series = $this->resolveSeries($family, $manufacturer, $seriesNode);

            // The AI sometimes puts variants directly on the series with no
            // "models" wrapper — synthesise a model so they still persist.
            $modelNodes = $seriesNode['models'] ?? [];
            if (empty($modelNodes) && ! empty($seriesNode['variants'])) {
                $modelNodes = [['model_number' => $seriesNode['series_name'] ?? null, 'variants' => $seriesNode['variants']]];
            }

            foreach ($modelNodes as $modelNode) {
                $model = $this->resolveModel($family, $manufacturer, $series, $modelNode);

                foreach ($modelNode['variants'] ?? [] as $variantNode) {
                    // Allow a per-variant manufacturer override.
                    $variantManufacturer = ! empty($variantNode['manufacturer'])
                        ? ($this->lookups->manufacturer((string) $variantNode['manufacturer']) ?? $manufacturer)
                        : $manufacturer;

                    $outcome = $this->persistVariant($family, $variantManufacturer, $model, $series, $variantNode);
                    $accepted  += $outcome === 'accepted' ? 1 : 0;
                    $duplicate += $outcome === 'duplicate' ? 1 : 0;
                    $rejected  += $outcome === 'rejected' ? 1 : 0;
                }
            }
        }

        return ['accepted' => $accepted, 'rejected' => $rejected, 'duplicate' => $duplicate];
    }

    /**
     * Resolve the manufacturer that applies to a series node: an explicit name
     * on the series, else on its first variant, else the response default.
     */
    private function manufacturerForNode(ProductFamily $family, array $seriesNode, ?Manufacturer $default): ?Manufacturer
    {
        $name = $seriesNode['manufacturer']
            ?? ($seriesNode['models'][0]['variants'][0]['manufacturer'] ?? null)
            ?? ($seriesNode['variants'][0]['manufacturer'] ?? null);

        if ($name) {
            $m = $this->lookups->manufacturer((string) $name);
            if ($m) {
                $family->manufacturers()->syncWithoutDetaching([
                    $m->id => ['source_type' => 'discovered_by_research'],
                ]);

                return $m;
            }
        }

        return $default;
    }

    private function resolveManufacturer(ProductFamily $family, ?array $node): ?Manufacturer
    {
        $name = $node['name'] ?? null;
        if (! $name) {
            return null;
        }

        $manufacturer = $this->lookups->manufacturer($name);
        if ($manufacturer && ! empty($node['official_website'])) {
            $manufacturer->official_website ??= $node['official_website'];
            $manufacturer->official_domain  ??= $this->sourceVerifier->extractDomain($node['official_website']);
            $manufacturer->save();
        }

        if ($manufacturer) {
            $family->manufacturers()->syncWithoutDetaching([
                $manufacturer->id => ['source_type' => 'discovered_by_research'],
            ]);
        }

        return $manufacturer;
    }

    private function resolveSeries(ProductFamily $family, ?Manufacturer $manufacturer, array $node): ?ProductSeries
    {
        if (! $manufacturer) {
            return null;
        }

        return ProductSeries::firstOrCreate(
            [
                'manufacturer_id'   => $manufacturer->id,
                'product_family_id' => $family->id,
                'series_name'       => $node['series_name'],
            ],
            [
                'official_product_name' => $node['official_product_name'] ?? null,
                'official_page_url'     => $node['official_page_url'] ?? null,
                'normalized_model_number' => $this->engine->normalizeToken($node['series_name'] ?? ''),
            ]
        );
    }

    private function resolveModel(ProductFamily $family, ?Manufacturer $manufacturer, ?ProductSeries $series, array $node): ?ProductModel
    {
        if (! $manufacturer || ! $series) {
            return null;
        }

        $model = ProductModel::firstOrCreate(
            [
                'product_series_id' => $series->id,
                'manufacturer_id'   => $manufacturer->id,
                'model_number'      => $node['model_number'] ?? $series->series_name,
            ],
            [
                'product_family_id' => $family->id,
                'port_type_id'      => $this->resolvePort($node['port_type'] ?? null),
                'pieces_count'      => $node['pieces'] ?? null,
                'operation_type_id' => null,
            ]
        );

        // Attach materials by component (multi-material via pivot; never copied
        // blindly between models).
        $this->attachMaterial($model, $node['body_material'] ?? null, 'body');
        $this->attachMaterial($model, $node['ball_material'] ?? null, 'ball');
        $this->attachMaterial($model, $node['seat_material'] ?? null, 'seat');

        return $model;
    }

    /**
     * Persist one variant. Returns 'accepted' | 'duplicate' | 'rejected'.
     */
    private function persistVariant(ProductFamily $family, ?Manufacturer $manufacturer, ?ProductModel $model, ?ProductSeries $series, array $node): string
    {
        if (! $manufacturer || ! $model) {
            return 'rejected';
        }

        // Build the canonical dedup / idempotency key.
        $sizeParts     = $this->engine->normalizeSize($node['size'] ?? ($node['dn_size'] ?? null));
        $connNorm      = $this->engine->normalizeConnection($node['connection'] ?? null);
        $pressureParts = $this->engine->normalizePressure($node['pressure_rating'] ?? null);

        $key = $this->engine->variantKey(
            $manufacturer->name,
            $model->model_number,
            $node['manufacturer_sku'] ?? null,
            $sizeParts['normalized'],
            $connNorm,
            $pressureParts['normalized'],
        );

        // Idempotency: skip if this exact variant already exists.
        if (ProductVariant::where('normalized_variant_key', $key)->exists()) {
            return 'duplicate';
        }

        // Was any cited source on the manufacturer's official domain?
        $officialMatched = false;
        foreach ($node['sources'] ?? [] as $src) {
            if ($this->sourceVerifier->matchesManufacturer($src['url'] ?? null, $manufacturer)) {
                $officialMatched = true;
                break;
            }
        }

        // Compute verification in code (rules, not AI trust).
        $assessment = $this->verification->assessVariant($node, $officialMatched);

        $sizeId     = $sizeParts['normalized'] !== ''
            ? optional($this->lookups->size($node['size'] ?? $node['dn_size'] ?? '', $sizeParts['normalized'], [
                'inch_decimal' => $sizeParts['inch'],
                'dn_value'     => $sizeParts['dn'],
            ]))->id
            : null;
        $connId     = $connNorm !== '' ? optional($this->lookups->connectionType($node['connection'] ?? ''))->id : null;
        $connStdId  = ! empty($node['connection_standard']) ? optional($this->lookups->connectionStandard($node['connection_standard']))->id : null;
        $pressureId = $pressureParts['normalized'] !== ''
            ? optional($this->lookups->pressure($node['pressure_rating'] ?? '', $pressureParts['normalized'], [
                'numeric_value'  => $pressureParts['numeric'],
                'unit'           => $pressureParts['unit'],
                'pressure_class' => $pressureParts['class'],
            ]))->id
            : null;

        $variant = ProductVariant::create([
            'product_model_id'         => $model->id,
            'product_family_id'        => $family->id,
            'manufacturer_id'          => $manufacturer->id,
            'manufacturer_sku'         => $node['manufacturer_sku'] ?? null,
            'manufacturer_part_number' => $node['manufacturer_part_number'] ?? null,
            'variant_name'             => trim(($model->model_number ?? '') . ' ' . ($node['size'] ?? '')),
            'normalized_variant_key'   => $key,
            'size_id'                  => $sizeId,
            'connection_type_id'       => $connId,
            'connection_standard_id'   => $connStdId,
            'pressure_rating_id'       => $pressureId,
            'temperature_min'          => $node['temperature_min'] ?? null,
            'temperature_max'          => $node['temperature_max'] ?? null,
            'temperature_unit'         => $node['temperature_unit'] ?? null,
            'verification_level'       => $assessment['level'],
            'verification_status'      => $assessment['status'],
            'availability_status'      => $this->safeAvailability($node['availability_status'] ?? null),
            'technical_notes'          => $assessment['reasons'] ? implode(' ', $assessment['reasons']) : null,
        ]);

        // Persist sources + evidence, then approvals/standards backed by them.
        $sourceMap = $this->persistSources($manufacturer, $series, $family, $node['sources'] ?? []);
        $this->persistApprovals($variant, $node['approvals'] ?? [], $sourceMap);
        $this->persistStandards($variant, $node['standards'] ?? [], $sourceMap);
        $this->persistEvidence($variant, $node, $sourceMap);

        return 'accepted';
    }

    /**
     * @param  list<array<string,mixed>>  $sources
     * @return array<int,int>  index → source_document_id
     */
    private function persistSources(?Manufacturer $manufacturer, ?ProductSeries $series, ProductFamily $family, array $sources): array
    {
        $map = [];
        foreach ($sources as $i => $src) {
            $type   = SourceTypeEnum::tryFrom($src['source_type'] ?? '') ?? SourceTypeEnum::Other;
            $doc = SourceDocument::create([
                'manufacturer_id'   => $manufacturer?->id,
                'product_family_id' => $family->id,
                'product_series_id' => $series?->id,
                'source_type'       => $type,
                'title'             => $src['title'] ?? null,
                'source_url'        => $src['url'] ?? null,
                'is_official'       => (bool) ($src['is_official'] ?? false),
            ]);
            // Re-verify officiality against the real domain (never trust the flag).
            $this->sourceVerifier->verifySource($doc);
            $map[$i] = $doc->id;
        }

        return $map;
    }

    private function persistApprovals(ProductVariant $variant, array $approvals, array $sourceMap): void
    {
        foreach ($approvals as $node) {
            $approval = $this->lookups->approval($node['name'] ?? '', $node['code'] ?? null);
            if (! $approval) {
                continue;
            }
            // Approvals require a supporting source (rule #3); use the first.
            $sourceId = $sourceMap[0] ?? null;
            $variant->approvals()->syncWithoutDetaching([
                $approval->id => [
                    'scope'               => $node['scope'] ?? null,
                    'source_id'           => $sourceId,
                    'verification_status' => $sourceId ? 'verified' : 'needs_review',
                ],
            ]);
        }
    }

    private function persistStandards(ProductVariant $variant, array $standards, array $sourceMap): void
    {
        foreach ($standards as $code) {
            $standard = $this->lookups->standard((string) $code);
            if ($standard) {
                $variant->standards()->syncWithoutDetaching([
                    $standard->id => ['source_id' => $sourceMap[0] ?? null],
                ]);
            }
        }
    }

    private function persistEvidence(ProductVariant $variant, array $node, array $sourceMap): void
    {
        foreach ($node['sources'] ?? [] as $i => $src) {
            $sourceId = $sourceMap[$i] ?? null;
            if (! $sourceId) {
                continue;
            }
            foreach (($src['supports_fields'] ?? []) as $field) {
                $variant->evidence()->create([
                    'source_document_id' => $sourceId,
                    'field_name'         => (string) $field,
                    'extracted_value'    => is_scalar($node[$field] ?? null) ? (string) $node[$field] : null,
                    'verification_status'=> 'pending',
                ]);
            }
        }
    }

    private function attachMaterial(?ProductModel $model, ?string $raw, string $component): void
    {
        if (! $model || ! $raw) {
            return;
        }
        $material = $this->lookups->material($raw);
        if ($material) {
            $model->materials()->syncWithoutDetaching([
                $material->id => ['component_type' => $component],
            ]);
        }
    }

    private function resolvePort(?string $raw): ?int
    {
        return $raw ? optional($this->lookups->portType($raw))->id : null;
    }

    /**
     * Map any availability wording onto a valid AvailabilityStatusEnum value so
     * the DB write (and enum cast) never fails on an unexpected label.
     */
    private function safeAvailability(?string $raw): string
    {
        $map = [
            'available' => 'current', 'in stock' => 'current', 'in production' => 'current',
            'active' => 'current', 'stock' => 'current', 'current' => 'current',
            'discontinued' => 'discontinued', 'obsolete' => 'discontinued', 'eol' => 'discontinued',
            'regional' => 'regional', 'limited' => 'regional',
        ];

        $key = strtolower(trim((string) $raw));

        return $map[$key] ?? 'unknown';
    }
}
