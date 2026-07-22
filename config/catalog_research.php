<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Product Catalog Research Module
    |--------------------------------------------------------------------------
    |
    | Configuration for the research/import module that sits on the dedicated
    | `catalog` database connection. This module never deals with prices.
    |
    */

    // Which DB connection the module's tables live on (matches config/database.php).
    'connection' => env('CATALOG_DB_CONNECTION_NAME', 'catalog'),

    // Where the module's migrations live, so the artisan command can target them.
    'migrations_path' => 'database/migrations/catalog',

    // Disk + folder for stored original uploads (never deleted).
    'storage' => [
        'disk'   => env('CATALOG_RESEARCH_DISK', 'local'),
        'folder' => 'imports/catalog-research',
    ],

    /*
    | AI research provider. The module is provider-agnostic (AiResearchProvider
    | interface); DeepSeek is the default implementation but can be swapped.
    */
    'provider' => env('CATALOG_RESEARCH_PROVIDER', 'deepseek'),

    // Staged research batch sizes (tunable without code changes).
    'batch' => [
        'manufacturers_per_batch'  => (int) env('CATALOG_RESEARCH_MANUFACTURERS_BATCH', 5),
        'series_per_batch'         => (int) env('CATALOG_RESEARCH_SERIES_BATCH', 10),
        'variants_per_validation'  => (int) env('CATALOG_RESEARCH_VARIANTS_BATCH', 20),
        'size'                     => (int) env('CATALOG_RESEARCH_BATCH_SIZE', 5),
        'max_concurrent_jobs'      => (int) env('CATALOG_RESEARCH_MAX_CONCURRENT_JOBS', 3),
    ],

    // Queue used for all research jobs (kept off the default queue).
    'queue' => env('CATALOG_RESEARCH_QUEUE', 'catalog-research'),

    // Stale-product refresh window for the optional scheduler.
    'source_refresh_days' => (int) env('CATALOG_SOURCE_REFRESH_DAYS', 180),

    /*
    | Sources that are NEVER acceptable as a final verification source. Used by
    | the VerificationService (rules enforced in code, not only in the prompt).
    */
    'blacklisted_source_domains' => [
        'amazon.', 'alibaba.', 'aliexpress.', 'ebay.', 'made-in-china.',
        'indiamart.', 'tradeindia.',
    ],

    /*
    | Permission map. No Spatie in this app — permissions are Gate abilities
    | defined in AppServiceProvider and resolved against the user_type.
    | 'admin' implicitly has every ability; employees get this subset.
    */
    'employee_permissions' => [
        'catalog.import.view', 'catalog.import.create', 'catalog.import.process',
        'catalog.family.view', 'catalog.family.manage',
        'catalog.research.start', 'catalog.research.pause', 'catalog.research.cancel',
        'catalog.research.retry',
        'catalog.product.view', 'catalog.product.manage',
        'catalog.product.verify', 'catalog.product.reject',
        'catalog.review.view', 'catalog.review.resolve',
        'catalog.source.view', 'catalog.source.manage',
        'catalog.export',
    ],
];
