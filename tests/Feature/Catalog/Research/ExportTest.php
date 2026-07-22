<?php

namespace Tests\Feature\Catalog\Research;

use App\Exports\Catalog\Research\ProductVariantsExport;
use App\Models\Catalog\Research\Manufacturer;
use App\Models\Catalog\Research\ProductFamily;
use App\Models\Catalog\Research\ProductModel;
use App\Models\Catalog\Research\ProductSeries;
use App\Models\Catalog\Research\ProductVariant;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExportTest extends CatalogResearchTestCase
{
    private function seedVariant(): void
    {
        $family = ProductFamily::create(['name' => 'Ball Valve', 'normalized_name' => 'ball valve', 'research_status' => 'verified', 'research_scope' => 'saudi']);
        $mfr    = Manufacturer::create(['name' => 'NIBCO', 'normalized_name' => 'nibco']);
        $series = ProductSeries::create(['manufacturer_id' => $mfr->id, 'product_family_id' => $family->id, 'series_name' => 'KT-585']);
        $model  = ProductModel::create(['product_series_id' => $series->id, 'manufacturer_id' => $mfr->id, 'product_family_id' => $family->id, 'model_number' => 'KT-585']);

        ProductVariant::create([
            'product_model_id' => $model->id, 'product_family_id' => $family->id, 'manufacturer_id' => $mfr->id,
            'manufacturer_sku' => 'NL95046', 'normalized_variant_key' => 'nibco|kt585|nl95046|0.5in|female-npt|300psi',
            'verification_level' => 'exact_manufacturer_sku', 'verification_status' => 'verified', 'availability_status' => 'current',
        ]);
    }

    public function test_export_has_no_price_columns_and_one_row_per_variant(): void
    {
        $this->seedVariant();

        $export = new ProductVariantsExport(ProductVariant::query());
        $path   = $export->toTempFile();

        $sheet   = IOFactory::load($path)->getActiveSheet();
        $headers = array_map(fn ($c) => strtolower((string) $c), $sheet->rangeToArray('A1:AH1')[0]);
        $headers = array_filter($headers, fn ($h) => $h !== null && $h !== '');

        // No price/cost/currency/tax/discount column anywhere. ("Market Scope" is
        // allowed — it is availability scope, not a Market *Price*.)
        foreach (['price', 'cost', 'currency', 'discount', 'tax', 'supplier price', 'market price'] as $banned) {
            foreach ($headers as $h) {
                $this->assertStringNotContainsString($banned, $h, "Export must not contain a '{$banned}' column.");
            }
        }

        // Header row + exactly one data row.
        $this->assertSame('NL95046', $sheet->getCell('G2')->getValue());
        $this->assertNull($sheet->getCell('A3')->getValue());

        @unlink($path);
    }
}
