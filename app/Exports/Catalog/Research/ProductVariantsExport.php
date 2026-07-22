<?php

namespace App\Exports\Catalog\Research;

use App\Models\Catalog\Research\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Exports product variants to XLSX — one variant per row, no multi-value cells,
 * and NO price columns of any kind. Streams via PhpSpreadsheet directly (no
 * maatwebsite/excel dependency). Chunks the query so large catalogs export
 * without loading everything into memory.
 */
class ProductVariantsExport
{
    /** Column headers — deliberately price-free (see spec §12). */
    private const HEADERS = [
        'Qimta Code', 'Base Row Code', 'Division', 'Category', 'Item Description',
        'Manufacturer', 'Manufacturer SKU', 'Manufacturer Part Number',
        'Series / Model', 'Product Name', 'Body Material', 'Ball Material',
        'Stem Material', 'Seat / Seal Material', 'Port Type', 'Pieces',
        'Connection Type', 'Connection Standard', 'Size', 'DN Size',
        'Pressure Rating', 'Temperature Rating', 'Operation Type',
        'Approval / Certification', 'Applicable Standard', 'Fire Protection Suitability',
        'Verification Level', 'Verification Status', 'Availability Status',
        'Market Scope', 'Official Source URL', 'Source Checked Date', 'Notes',
    ];

    public function __construct(private Builder $query) {}

    /** Write the export to a temp file and return its absolute path. */
    public function toTempFile(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Products');

        // Header row.
        foreach (self::HEADERS as $i => $header) {
            $sheet->setCellValue([$i + 1, 1], $header);
        }

        $row = 2;
        $this->query
            ->with([
                'family.division', 'family.category', 'manufacturer', 'model.series',
                'model.materials', 'model.portType', 'model.operationType',
                'size', 'connectionType', 'connectionStandard', 'pressureRating',
                'approvals', 'standards', 'evidence.source',
            ])
            ->chunk(500, function ($variants) use ($sheet, &$row) {
                foreach ($variants as $variant) {
                    foreach (array_values($this->rowFor($variant)) as $i => $value) {
                        $sheet->setCellValue([$i + 1, $row], $value);
                    }
                    $row++;
                }
            });

        $path = tempnam(sys_get_temp_dir(), 'catalog_export_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return $path;
    }

    /** @return array<string,string> one flat row; never multi-value cells. */
    private function rowFor(ProductVariant $v): array
    {
        $model  = $v->model;
        $series = $model?->series;

        $material = fn (string $component) => optional(
            $model?->materials->first(fn ($m) => $m->pivot->component_type === $component)
        )->name ?? '';

        $officialSource = $v->evidence
            ->map(fn ($e) => $e->source)
            ->filter(fn ($s) => $s && $s->is_official)
            ->first();

        return [
            'qimta_code'          => (string) ($v->family?->source_code ?? ''),
            'base_row_code'       => (string) ($v->family?->source_code ?? ''),
            'division'            => (string) ($v->family?->division?->name ?? ''),
            'category'            => (string) ($v->family?->category?->name ?? ''),
            'item_description'    => (string) ($v->family?->name ?? ''),
            'manufacturer'        => (string) ($v->manufacturer?->name ?? ''),
            'manufacturer_sku'    => (string) ($v->manufacturer_sku ?? ''),
            'part_number'         => (string) ($v->manufacturer_part_number ?? ''),
            'series_model'        => (string) ($series?->series_name ?? $model?->model_number ?? ''),
            'product_name'        => (string) ($model?->product_name ?? ''),
            'body_material'       => $material('body'),
            'ball_material'       => $material('ball'),
            'stem_material'       => $material('stem'),
            'seat_material'       => $material('seat') ?: $material('seal'),
            'port_type'           => (string) ($model?->portType?->name ?? ''),
            'pieces'              => (string) ($model?->pieces_count ?? ''),
            'connection_type'     => (string) ($v->connectionType?->name ?? ''),
            'connection_standard' => (string) ($v->connectionStandard?->name ?? ''),
            'size'                => (string) ($v->size?->display_value ?? ''),
            'dn_size'             => (string) ($v->size?->dn_value ?? ''),
            'pressure_rating'     => (string) ($v->pressureRating?->rating_name ?? ''),
            'temperature'         => $this->temperature($v),
            'operation_type'      => (string) ($model?->operationType?->name ?? ''),
            'approvals'           => $v->approvals->map(fn ($a) => trim($a->name . ' ' . ($a->approval_code ?? '')))->implode('; '),
            'standards'           => $v->standards->pluck('code')->implode('; '),
            'fire_protection'     => $this->fireProtection($v) ? 'Yes' : 'No',
            'verification_level'  => $v->verification_level?->label() ?? '',
            'verification_status' => $v->verification_status?->label() ?? '',
            'availability_status' => $v->availability_status?->label() ?? '',
            'market_scope'        => (string) ($v->market_scope ?? ''),
            'source_url'          => (string) ($officialSource?->source_url ?? ''),
            'source_checked'      => (string) ($officialSource?->checked_at?->format('Y-m-d') ?? ''),
            'notes'               => (string) ($v->technical_notes ?? ''),
        ];
    }

    private function temperature(ProductVariant $v): string
    {
        if ($v->temperature_min === null && $v->temperature_max === null) {
            return '';
        }

        return trim(sprintf('%s..%s %s', $v->temperature_min, $v->temperature_max, $v->temperature_unit ?? ''));
    }

    private function fireProtection(ProductVariant $v): bool
    {
        return $v->approvals->contains(fn ($a) => in_array($a->issuing_body, ['UL', 'FM', 'LPCB'], true));
    }
}
