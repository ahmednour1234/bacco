<?php

namespace App\Console\Commands\Catalog;

use Database\Seeders\Catalog\Research\CatalogLookupSeeder;
use Illuminate\Console\Command;

/**
 * Runs the research-catalog module migrations against the dedicated `catalog`
 * connection (they are not auto-discovered, matching how the existing catalog
 * migrations are run). Optionally seeds the reference dictionaries.
 *
 *   php artisan catalog:research-migrate            # migrate
 *   php artisan catalog:research-migrate --seed     # migrate + seed lookups
 *   php artisan catalog:research-migrate --fresh    # rollback + migrate (danger)
 *   php artisan catalog:research-migrate --rollback # rollback only
 */
class CatalogResearchMigrate extends Command
{
    protected $signature = 'catalog:research-migrate
        {--seed : Seed reference dictionaries after migrating}
        {--fresh : Roll back this module\'s migrations then re-run them}
        {--rollback : Roll back this module\'s migrations only}';

    protected $description = 'Migrate (and optionally seed) the Product Catalog Research module on the catalog connection.';

    public function handle(): int
    {
        $connection = config('catalog_research.connection', 'catalog');
        $path       = config('catalog_research.migrations_path', 'database/migrations/catalog');

        $opts = ['--database' => $connection, '--path' => $path, '--force' => true];

        if ($this->option('rollback') || $this->option('fresh')) {
            $this->info("Rolling back catalog research migrations on [{$connection}]…");
            $this->call('migrate:rollback', $opts);

            if ($this->option('rollback')) {
                return self::SUCCESS;
            }
        }

        $this->info("Migrating catalog research module on [{$connection}] from [{$path}]…");
        $this->call('migrate', $opts);

        if ($this->option('seed')) {
            $this->info('Seeding reference dictionaries…');
            $this->call('db:seed', ['--class' => CatalogLookupSeeder::class, '--force' => true]);
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
