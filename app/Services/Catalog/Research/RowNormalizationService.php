<?php

namespace App\Services\Catalog\Research;

/**
 * Turns a raw Excel row (assoc: header => value) plus a column mapping into a
 * structured, normalized representation. It ONLY reshapes and canonicalizes the
 * data that is present — it never invents manufacturers, sizes or SKUs, and it
 * never produces cartesian combinations. Multi-value cells are split into lists
 * while the original raw text is preserved for review.
 */
class RowNormalizationService
{
    public function __construct(
        private ColumnMappingService $mapping,
        private NormalizationEngine  $engine,
    ) {}

    /**
     * @param  array<string,string>  $rawRow  header => value
     * @param  array<string,string>  $map     header => canonical field
     * @return array{
     *   fields: array<string,string>,
     *   manufacturers: list<array{name:string, type:string}>,
     *   normalized: array<string,mixed>,
     *   item_description: string
     * }
     */
    public function normalize(array $rawRow, array $map): array
    {
        // Pivot header=>value into canonicalField=>value.
        $fields = [];
        foreach ($map as $header => $target) {
            $fields[$target] = $rawRow[$header] ?? '';
        }

        // Extract manufacturer lists (multi-value → records), tagged by type.
        $manufacturers = [];
        foreach ($this->mapping->manufacturerFields() as $field => $type) {
            foreach ($this->engine->splitMultiValue($fields[$field] ?? null) as $name) {
                $manufacturers[] = ['name' => $name, 'type' => $type];
            }
        }

        // Canonicalize the individual spec values (kept alongside raw).
        $sizeParts     = $this->engine->normalizeSize($fields['size'] ?? null);
        $pressureParts = $this->engine->normalizePressure($fields['pressure_rating'] ?? null);

        $normalized = [
            'materials'   => $this->engine->splitMultiValue($fields['type_of_material'] ?? null),
            'connections' => $this->engine->splitMultiValue($fields['connection_type'] ?? null),
            'sizes'       => $this->engine->splitMultiValue($fields['size'] ?? null),
            'standards'   => $this->engine->splitMultiValue($fields['standard_code'] ?? null),
            'approvals'   => $this->engine->splitMultiValue($fields['approval'] ?? null),
            'size_canonical'     => $sizeParts,
            'pressure_canonical' => $pressureParts,
            'normalized_name'    => $this->engine->normalizeText($fields['item_description'] ?? ''),
        ];

        return [
            'fields'           => $fields,
            'manufacturers'    => $manufacturers,
            'normalized'       => $normalized,
            'item_description' => trim($fields['item_description'] ?? ''),
        ];
    }
}
