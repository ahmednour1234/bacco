<?php

namespace App\Services\Catalog\Research;

use App\Models\Catalog\Research\CatalogImport;
use App\Repositories\Catalog\Research\CatalogImportRepository;

/**
 * Owns the canonical list of mappable target fields and the persistence of a
 * column mapping on an import. A mapping is Excel-header → canonical-field, e.g.
 *   ["Item Description" => "item_description", "Local Manufacturers" => "local_manufacturers"]
 * The saved mapping is reusable across imports of similar files.
 */
class ColumnMappingService
{
    public function __construct(private CatalogImportRepository $importRepo) {}

    /**
     * Canonical target fields the importer understands. Grouped for the UI.
     * `manufacturer_group` fields are all treated as manufacturer sources but
     * tag the discovered manufacturer with a region/type.
     *
     * @return array<string,array{label:string, group:string, manufacturer_type?:string}>
     */
    public function targetFields(): array
    {
        return [
            'qimta_code'        => ['label' => 'Qimta Code',        'group' => 'identity'],
            'division'          => ['label' => 'Division',          'group' => 'taxonomy'],
            'category'          => ['label' => 'Category',          'group' => 'taxonomy'],
            'item_description'  => ['label' => 'Item Description',   'group' => 'identity'],
            'sub_type'          => ['label' => 'Sub-Type',          'group' => 'identity'],
            'product_name'      => ['label' => 'Product Name',      'group' => 'identity'],
            'type_of_material'  => ['label' => 'Type of Material',  'group' => 'spec'],
            'connection_type'   => ['label' => 'Connection Type',   'group' => 'spec'],
            'pressure_rating'   => ['label' => 'Pressure/Rating',   'group' => 'spec'],
            'standard_code'     => ['label' => 'Standard/Code',     'group' => 'spec'],
            'color_finish'      => ['label' => 'Color/Finish',      'group' => 'spec'],
            'size'              => ['label' => 'Size',              'group' => 'spec'],
            'unit'              => ['label' => 'Unit',              'group' => 'spec'],
            'approval'          => ['label' => 'Approval',          'group' => 'spec'],

            'local_manufacturers'  => ['label' => 'Local Manufacturers',  'group' => 'manufacturers', 'manufacturer_type' => 'saudi'],
            'gcc_manufacturers'    => ['label' => 'GCC Manufacturers',    'group' => 'manufacturers', 'manufacturer_type' => 'gcc'],
            'chinese_manufacturers'=> ['label' => 'Chinese Manufacturers','group' => 'manufacturers', 'manufacturer_type' => 'chinese'],
            'global_manufacturers' => ['label' => 'Global Manufacturers', 'group' => 'manufacturers', 'manufacturer_type' => 'global'],
        ];
    }

    /** Field keys that carry manufacturer lists, with their manufacturer_type. */
    public function manufacturerFields(): array
    {
        $out = [];
        foreach ($this->targetFields() as $key => $meta) {
            if (($meta['group'] ?? null) === 'manufacturers') {
                $out[$key] = $meta['manufacturer_type'] ?? 'unknown';
            }
        }

        return $out;
    }

    /**
     * Validate an incoming mapping against known target fields and headers.
     * Returns the cleaned mapping (drops empty targets).
     *
     * @param  array<string,string>  $mapping  header => targetField
     * @param  list<string>          $headers  actual sheet headers
     * @return array<string,string>
     */
    public function sanitize(array $mapping, array $headers): array
    {
        $targets = $this->targetFields();
        $clean   = [];

        foreach ($mapping as $header => $target) {
            $target = trim((string) $target);
            if ($target === '' || ! isset($targets[$target])) {
                continue;
            }
            if (! in_array($header, $headers, true)) {
                continue;
            }
            $clean[$header] = $target;
        }

        return $clean;
    }

    /** Persist the mapping (plus the sheet + header row it applies to). */
    public function save(CatalogImport $import, string $sheetName, int $headerRow, array $mapping): CatalogImport
    {
        return $this->importRepo->update($import, [
            'column_mapping' => [
                'sheet'      => $sheetName,
                'header_row' => $headerRow,
                'map'        => $mapping,
            ],
        ]);
    }

    /** True when the import has enough mapping to run (needs item_description). */
    public function isMappable(CatalogImport $import): bool
    {
        $map = $import->column_mapping['map'] ?? [];

        return in_array('item_description', $map, true);
    }
}
