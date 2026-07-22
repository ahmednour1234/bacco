<?php

namespace App\Services\Catalog\Research\DeepSeek\Prompts;

/**
 * Prompts for Deep Catalog Expansion.
 *
 * Expansion grows the catalog by asking the model to ENUMERATE what a
 * manufacturer actually publishes — never to combine attributes. The
 * distinction is the whole point:
 *
 *   forbidden : "this valve probably comes in 1/2, 3/4, 1 inch"  → invention
 *   required  : "the official catalog lists these sizes"          → enumeration
 *
 * The model is treated as a source of knowledge ABOUT the market, not as a
 * generator of plausible combinations.
 */
class CatalogExpansionPromptBuilder
{
    /**
     * Shared rules appended to every expansion prompt. Deliberately stricter
     * than discovery: expansion runs at volume, so a single loose rule would
     * multiply into thousands of invented rows.
     */
    public function systemPrompt(): string
    {
        return <<<'PROMPT'
        You are a Product Catalog Expansion Agent.

        Your job is to ENUMERATE products that a manufacturer actually publishes.
        You are NOT allowed to generate combinations.

        ABSOLUTE RULES:
        1. Never invent a SKU, model number, size, pressure or approval.
        2. Never expand a product by multiplying attributes together
           (Size x Connection x Pressure is FORBIDDEN).
        3. Only list a size if the manufacturer publishes that size for that
           specific model. If the catalog states a range, say so explicitly and
           list only the discrete sizes the catalog itself names.
        4. Every product you return MUST carry at least one official source URL.
           If you cannot name a source, do not return the product.
        5. If you are unsure whether a product exists, omit it. A short accurate
           list is correct; a long speculative list is a failure.
        6. Prefer the manufacturer's own website, catalog PDF or datasheet.
           Distributors are acceptable only as secondary evidence and must be
           marked as such.

        verification_level rules:
        - "exact_manufacturer_sku"  → you have the real published SKU.
        - "official_model_and_size" → official model plus an officially listed size.
        - "official_series_range"   → the catalog gives a size range for the series
                                      but no per-size SKU.
        - "distributor_only"        → only a distributor lists it.
        Never claim a stronger level than your evidence supports.

        Return ONLY JSON. No markdown, no code fences, no commentary.
        PROMPT;
    }

    /**
     * Ask for a slice of one manufacturer's real catalog within a category.
     *
     * Paginated by design: asking for "everything" invites the model to pad the
     * list, while a bounded page keeps each answer verifiable.
     *
     * @param  list<string>  $knownModels  models already stored, so it skips them
     */
    public function manufacturerSweep(
        string $manufacturer,
        ?string $website,
        string $category,
        int $page,
        int $perPage,
        array $knownModels = [],
    ): string {
        $site  = $website ? "Official website: {$website}" : 'Official website: unknown';
        $known = $knownModels === []
            ? '(none yet)'
            : implode(', ', array_slice($knownModels, 0, 60));

        $offset = ($page - 1) * $perPage;

        return <<<PROMPT
        Manufacturer: {$manufacturer}
        {$site}
        Product category: {$category}

        List real products this manufacturer publishes in this category.

        This is page {$page} (items {$offset}+). Return up to {$perPage} product
        series — return as many REAL ones as you can document, not fewer.
        Skip these models, already recorded: {$known}

        Cover the manufacturer's range broadly: different product lines, model
        families and sub-categories within this category, not several near-copies
        of the same series.

        For each series give: series_name, official_product_name, official_page_url,
        and its models. For each model give the real model_number, materials, port
        type, pieces and operation type where published.

        For each model, list variants ONLY where the manufacturer publishes them:
        - If per-size SKUs exist, return each SKU as its own variant with
          verification_level = "exact_manufacturer_sku".
        - If only a size range is published, return ONE variant describing the
          range with verification_level = "official_series_range" and leave
          manufacturer_sku null.

        Do NOT pad the list to reach {$perPage}. If this manufacturer publishes
        fewer products in this category, return fewer and set warnings
        accordingly. Return an empty series array if there are no more products.

        Return the standard catalog JSON schema.
        PROMPT;
    }

    /**
     * Ask which sizes are officially published for one specific model.
     *
     * This is the safe way to multiply row counts: the sizes come from the
     * manufacturer's own table, so each resulting variant is documented rather
     * than assumed.
     */
    public function sizeRangeExpansion(
        string $manufacturer,
        string $modelNumber,
        ?string $seriesName,
        ?string $sourceUrl,
    ): string {
        $series = $seriesName ? "Series: {$seriesName}" : '';
        $source = $sourceUrl ? "Known source: {$sourceUrl}" : '';

        return <<<PROMPT
        Manufacturer: {$manufacturer}
        Model number: {$modelNumber}
        {$series}
        {$source}

        Question: which sizes does the manufacturer OFFICIALLY publish for this
        exact model, and does each published size have its own SKU?

        Rules:
        - List only sizes named in an official catalog, datasheet or product page.
        - If each size has its own SKU, return one variant per size with that SKU
          and verification_level = "exact_manufacturer_sku".
        - If sizes are published but without per-size SKUs, return one variant per
          published size with manufacturer_sku = null and verification_level =
          "official_model_and_size".
        - If only a range is stated (for example "1/2 inch to 4 inch") and the
          individual sizes are NOT enumerated, return a SINGLE variant describing
          the range with verification_level = "official_series_range". Do NOT
          expand the range into individual sizes yourself.
        - Every variant must cite the source URL that lists these sizes.

        Never assume every connection type is available in every size. Only pair a
        size with a connection when the source shows that pairing.

        Return the standard catalog JSON schema.
        PROMPT;
    }
}
