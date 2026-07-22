<?php

namespace Tests\Feature\Catalog\Research;

use App\Jobs\Catalog\Research\ProcessCatalogResearchImportJob;
use App\Models\Catalog\Research\CatalogImport;
use App\Models\Catalog\Research\CatalogImportRow;
use App\Models\Catalog\Research\ProductFamily;
use App\Services\Catalog\Research\ExcelReaderService;
use App\Services\Catalog\Research\ImportReport;
use App\Services\Catalog\Research\NormalizationEngine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * End-to-end coverage for the research Excel import pipeline. The `catalog`
 * connection is pointed at an in-memory sqlite DB and this module's migrations
 * are run against it (the existing pricing catalog migrations are MySQL-only,
 * so only this module's files are migrated here).
 */
class ExcelImportTest extends TestCase
{
    private string $catalogDb;

    protected function setUp(): void
    {
        parent::setUp();

        // A temp-file sqlite DB (not :memory:) so every resolution of the
        // catalog connection shares one database within the test.
        $this->catalogDb = tempnam(sys_get_temp_dir(), 'catalog_test_') . '.sqlite';
        touch($this->catalogDb);

        config()->set('database.connections.catalog', [
            'driver'   => 'sqlite',
            'database' => $this->catalogDb,
            'prefix'   => '',
            'foreign_key_constraints' => false,
        ]);
        config()->set('catalog_research.storage.disk', 'local');
        DB::purge('catalog');

        $this->migrateCatalogModule();
    }

    protected function tearDown(): void
    {
        DB::purge('catalog');
        @unlink($this->catalogDb);

        parent::tearDown();
    }

    private function migrateCatalogModule(): void
    {
        $files = glob(database_path('migrations/catalog/2026_07_22_*.php'));
        sort($files);
        foreach ($files as $file) {
            $migration = require $file;
            $migration->up();
        }
    }

    private function makeImport(string $csv): CatalogImport
    {
        Storage::fake('local');
        Storage::disk('local')->put('imports/catalog-research/test.csv', $csv);

        return CatalogImport::create([
            'original_file_name' => 'test.csv',
            'stored_file_path'   => 'imports/catalog-research/test.csv',
            'file_type'          => 'csv',
            'file_size'          => strlen($csv),
            'sheets_count'       => 1,
            'status'             => 'uploaded',
            'column_mapping'     => [
                'sheet'      => 'Worksheet',
                'header_row' => 1,
                'map'        => [
                    'Qimta Code'          => 'qimta_code',
                    'Division'            => 'division',
                    'Item Description'    => 'item_description',
                    'Type of Material'    => 'type_of_material',
                    'Connection Type'     => 'connection_type',
                    'Pressure/Rating'     => 'pressure_rating',
                    'Size'                => 'size',
                    'Unit'                => 'unit',
                    'Global Manufacturers'=> 'global_manufacturers',
                ],
            ],
        ]);
    }

    private function runImport(CatalogImport $import): void
    {
        // The CSV reader names the sheet 'Worksheet'; align the mapping to it.
        $abs    = Storage::disk('local')->path($import->stored_file_path);
        $sheets = app(ExcelReaderService::class)->sheetNames($abs);
        $cm     = $import->column_mapping;
        $cm['sheet'] = $sheets[0];
        $import->update(['column_mapping' => $cm]);

        app()->call([app(ProcessCatalogResearchImportJob::class, ['importId' => $import->id]), 'handle']);
    }

    public function test_import_creates_families_not_variants_and_detects_duplicates(): void
    {
        $csv = <<<CSV
        Qimta Code,Division,Item Description,Type of Material,Connection Type,Pressure/Rating,Size,Unit,Global Manufacturers
        FF-001,Fire Fighting,Ball Valve (Brass),Brass / Bronze,"Threaded, Press",300 PSI,"1 1/4""",Each,"NIBCO, KITZ, Victaulic"
        FF-001,Fire Fighting,Ball Valve (Brass),Brass / Bronze,"Threaded, Press",300 PSI,"1 1/4""",Each,"NIBCO, KITZ, Victaulic"
        FF-002,Fire Fighting,Gate Valve,Bronze,Flanged,PN16,DN50,Each,Hattersley
        FF-003,Fire Fighting,,Brass,Threaded,600 WOG,2 inch,Each,NIBCO
        CSV;

        $import = $this->makeImport($csv);
        $this->runImport($import);
        $import->refresh();

        // 4 data rows total.
        $this->assertSame(4, $import->total_rows);
        // 2 imported (Ball Valve + Gate Valve), 1 duplicate, 1 missing-description.
        $this->assertSame(2, $import->imported_rows);
        $this->assertSame(1, $import->duplicate_rows);
        $this->assertSame(1, $import->failed_rows);

        // Families created — but NO variants (the product_variants table is empty).
        $this->assertSame(2, ProductFamily::count());
        $this->assertSame(0, \App\Models\Catalog\Research\ProductVariant::count());

        // Raw rows preserved for every input row (never deleted).
        $this->assertSame(4, CatalogImportRow::count());

        // Manufacturers split into records and linked via pivot.
        $ballValve = ProductFamily::where('name', 'Ball Valve (Brass)')->first();
        $this->assertNotNull($ballValve);
        $this->assertEqualsCanonicalizing(
            ['NIBCO', 'KITZ', 'Victaulic'],
            $ballValve->manufacturers()->pluck('name')->all()
        );

        // Report matches the spec fields.
        $report = app(ImportReport::class)->forImport($import);
        $this->assertSame(1, $report['rows_missing_description']);
        $this->assertSame(2, $report['rows_ready_for_research']);
    }

    public function test_size_normalization_unifies_equivalent_forms(): void
    {
        $engine = app(NormalizationEngine::class);

        $a = $engine->normalizeSize('1 1/4"')['normalized'];
        $b = $engine->normalizeSize('1¼"')['normalized'];
        $c = $engine->normalizeSize('1.25 inch')['normalized'];

        $this->assertSame($a, $b);
        $this->assertSame($b, $c);
    }

    public function test_connection_normalization_unifies_npt_variants(): void
    {
        $engine = app(NormalizationEngine::class);

        $this->assertSame(
            $engine->normalizeConnection('N.P.T.'),
            $engine->normalizeConnection('NPT')
        );
        $this->assertSame(
            $engine->normalizeConnection('Female NPT'),
            $engine->normalizeConnection('FNPT')
        );
    }

    public function test_variant_key_shape(): void
    {
        $engine = app(NormalizationEngine::class);

        $this->assertSame(
            'nibco|kt58570ul|nl95046|0.5in|female-npt|300psi',
            $engine->variantKey('NIBCO', 'KT-585-70-UL', 'NL95046', '0.5in', 'female-npt', '300psi')
        );
    }

    public function test_pressure_normalization(): void
    {
        $engine = app(NormalizationEngine::class);

        $this->assertSame('300psi', $engine->normalizePressure('300 PSI')['normalized']);
        $this->assertSame('600wog', $engine->normalizePressure('600 WOG')['normalized']);
        $this->assertSame('pn16', $engine->normalizePressure('PN16')['normalized']);
    }
}
