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

    /**
     * Best-effort automatic mapping: match each spreadsheet header to a target
     * field by keyword, so the user usually only needs to confirm. Returns
     * header => targetField. Supports English + common Arabic labels.
     *
     * @param  list<string>  $headers
     * @return array<string,string>
     */
    public function autoMap(array $headers): array
    {
        // Ordered: more specific matches first so "product name" doesn't grab
        // a plain "product" meant for something else.
        $rules = [
            'qimta_code'            => ['qimta', 'رمز قيمتا', 'كود', 'رمز المنتج', 'code'],
            'item_description'      => ['item description', 'description', 'وصف', 'اسم الصنف'],
            'product_name'          => ['product name', 'اسم المنتج'],
            'sub_type'              => ['sub-type', 'sub type', 'النوع الفرعي'],
            'division'              => ['division', 'القسم', 'الشعبة'],
            'category'              => ['category', 'الفئة', 'التصنيف'],
            'type_of_material'      => ['material', 'الخامة', 'نوع المادة', 'المادة'],
            'connection_type'       => ['connection', 'التوصيل', 'الوصلة'],
            'pressure_rating'       => ['pressure', 'rating', 'الضغط', 'التصنيف الضغطي'],
            'standard_code'         => ['standard', 'المعيار', 'الكود القياسي'],
            'color_finish'          => ['color', 'finish', 'اللون', 'التشطيب'],
            'size'                  => ['size', 'المقاس', 'الحجم'],
            'unit'                  => ['unit', 'الوحدة'],
            'approval'              => ['approval', 'certification', 'الاعتماد', 'الشهادة'],
            'local_manufacturers'   => ['local manufacturer', 'saudi', 'مصنعين محليين', 'محلي'],
            'gcc_manufacturers'     => ['gcc manufacturer', 'خليجي', 'دول الخليج'],
            'chinese_manufacturers' => ['chinese manufacturer', 'china', 'صيني'],
            'global_manufacturers'  => ['global manufacturer', 'approved maker', 'maker', 'brand', 'عالمي', 'المصنعين'],
        ];

        $targets = $this->targetFields();
        $map     = [];
        $used    = [];

        foreach ($headers as $header) {
            $h = mb_strtolower(trim($header));
            if ($h === '') {
                continue;
            }
            foreach ($rules as $field => $needles) {
                if (isset($used[$field]) || ! isset($targets[$field])) {
                    continue;
                }
                foreach ($needles as $needle) {
                    if (str_contains($h, mb_strtolower($needle))) {
                        $map[$header] = $field;
                        $used[$field] = true;
                        break 2;
                    }
                }
            }
        }

        return $map;
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
