<?php

namespace Tests\Feature\Catalog\Research;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Base test case for the research module: points the `catalog` connection at a
 * temp-file sqlite DB (not :memory:, which resets per resolution) and runs this
 * module's migrations against it. The existing pricing catalog migrations are
 * MySQL-only, so only this module's 2026_07_22_* files are applied.
 */
abstract class CatalogResearchTestCase extends TestCase
{
    protected string $catalogDb;

    protected function setUp(): void
    {
        parent::setUp();

        $this->catalogDb = tempnam(sys_get_temp_dir(), 'catalog_test_') . '.sqlite';
        touch($this->catalogDb);

        config()->set('database.connections.catalog', [
            'driver'                  => 'sqlite',
            'database'                => $this->catalogDb,
            'prefix'                  => '',
            'foreign_key_constraints' => false,
        ]);
        config()->set('catalog_research.storage.disk', 'local');
        config()->set('catalog_research.provider', 'fake');
        DB::purge('catalog');

        foreach ($this->catalogMigrationFiles() as $file) {
            (require $file)->up();
        }
    }

    protected function tearDown(): void
    {
        DB::purge('catalog');
        @unlink($this->catalogDb);

        parent::tearDown();
    }

    /** @return list<string> */
    private function catalogMigrationFiles(): array
    {
        $files = glob(database_path('migrations/catalog/2026_07_22_*.php'));
        sort($files);

        return $files;
    }
}
