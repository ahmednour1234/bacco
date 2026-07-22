<?php

namespace App\Services\Catalog\Research\DeepSeek\Prompts;

use App\Enums\Catalog\Research\ResearchJobTypeEnum;
use App\Services\Catalog\Research\DeepSeek\Dto\ResearchRequest;

/**
 * Builds the system + user prompts sent to the AI. The system prompt is the
 * first line of defence against hallucination (the code enforces the rest).
 * Research is split into stages so a whole family is never sent in one prompt.
 */
class ResearchPromptBuilder
{
    /**
     * The catalog research agent system prompt. Forbids inventing products and
     * fixes the exact JSON contract. Kept in one place so every stage shares it.
     */
    public function systemPrompt(): string
    {
        return <<<'PROMPT'
        You are a Product Catalog Research Agent.

        Your task is to discover REAL products that exist in the market — not
        calculated possibilities. You MUST NOT create a product by combining Size,
        Pressure and Connection by default. You MUST NOT produce a cartesian
        product of attributes.

        Only return a product if you found at least ONE of:
        - Manufacturer SKU
        - Manufacturer Part Number
        - Official Model Number
        - Official Product Page
        - Official Catalog PDF
        - Official Datasheet
        - Official Certification Document

        Source priority (highest first):
        1. Official Manufacturer Product Page
        2. Official Manufacturer PDF Catalog
        3. Official Manufacturer Datasheet
        4. Official Certification Database
        5. Official Regional Manufacturer Website
        6. Authorized Distributor
        7. Other sources — for discovery only, never for final verification.

        NEVER treat a blog, marketplace, Amazon, Alibaba, a random reseller, or an
        AI-generated article as a final verification source.

        If you cannot confirm a value, return null. Never guess SKU, Size,
        Pressure, Approval, Material, Country, Certification or Connection Type.

        Separate Series-level data from SKU-level data:
        - If a page shows only a size RANGE with no independent SKU, set
          verification_level = "official_series_range".
        - If there is a real Manufacturer SKU, set
          verification_level = "exact_manufacturer_sku".
        - If the information is from a distributor only, set
          verification_level = "distributor_only".

        Approvals must be classified precisely. "UL 258" (sprinkler trim) is NOT
        the same as "UL 842" (flammable fluids); NSF is potable water; WRAS is
        water regulations. Never assume an approval from the word "UL" alone.

        Return ONLY JSON — no markdown, no commentary, no code fences.
        PROMPT;
    }

    /** The stage-specific user prompt. */
    public function userPrompt(ResearchRequest $request): string
    {
        return match ($request->type) {
            ResearchJobTypeEnum::DiscoverManufacturers => $this->discoverManufacturers($request),
            ResearchJobTypeEnum::DiscoverProductSeries => $this->discoverSeries($request),
            ResearchJobTypeEnum::DiscoverModels        => $this->discoverModels($request),
            ResearchJobTypeEnum::DiscoverVariants      => $this->discoverVariants($request),
            ResearchJobTypeEnum::VerifyProduct,
            ResearchJobTypeEnum::VerifyApproval,
            ResearchJobTypeEnum::VerifySource          => $this->verify($request),
            default                                     => $this->discoverVariants($request),
        } . "\n\n" . $this->schemaReminder();
    }

    private function discoverManufacturers(ResearchRequest $r): string
    {
        $scope = $r->marketScope ? " Prioritise the {$r->marketScope} market." : '';

        // One comprehensive pass: real manufacturers AND their real, documented
        // products — with SKU, size, connection, pressure, materials, approvals
        // and the official source URL each fact came from. This fills the whole
        // catalog in a single response so nothing is left half-discovered.
        return "Discover REAL, documented products for the product type: \"{$r->familyName}\".{$scope}\n\n"
            . "For each manufacturer that actually makes this product, return its "
            . "official website and country, then its real product series/models, "
            . "and for each model the real variants (SKUs) you can document. For "
            . "every variant provide: manufacturer SKU or official model number, "
            . "size (and DN size), connection type, connection standard, pressure "
            . "rating, body/ball/seat materials, port type, pieces, operation type "
            . "and approvals (with the exact code, e.g. UL 258 vs UL 842).\n\n"
            . "CRITICAL: every variant MUST include at least one official source in "
            . "its \"sources\" array — the manufacturer product page, catalog PDF, "
            . "datasheet or certificate URL that documents it. If you cannot find a "
            . "real source for a value, set that value to null. Never invent a SKU, "
            . "size, approval or URL. Do not expand a size range into invented SKUs. "
            . "Return several manufacturers and as many documented variants as you "
            . "can confirm, following the JSON schema exactly.";
    }

    private function discoverSeries(ResearchRequest $r): string
    {
        return "Stage 2 — Discover product series for manufacturer \"{$r->manufacturerName}\".\n"
            . "For the product type \"{$r->familyName}\", list this manufacturer's real "
            . "product series / model families, with official product pages and official "
            . "catalog URLs. Provide model numbers only if they are officially published.";
    }

    private function discoverModels(ResearchRequest $r): string
    {
        $series = $r->context['series_name'] ?? 'the given series';

        return "Stage 3 — Discover models for series \"{$series}\" by \"{$r->manufacturerName}\".\n"
            . "List the base models (before size variation) with body/ball/seat "
            . "materials, port type, pieces and operation type — each only if "
            . "officially documented.";
    }

    private function discoverVariants(ResearchRequest $r): string
    {
        $series = $r->context['series_name'] ?? 'the given series';

        return "Stage 3/4 — Discover real variants (SKUs) for series \"{$series}\" by \"{$r->manufacturerName}\".\n"
            . "For each real, documented variant provide: manufacturer SKU, size, "
            . "dn size, connection, connection standard, pressure rating, material, "
            . "port type, pieces and approvals — each backed by an official source. "
            . "Do NOT expand a published size range into invented SKUs.";
    }

    private function verify(ResearchRequest $r): string
    {
        return "Stage 4 — Verify.\n"
            . "Independently verify the following item against official sources and "
            . "return the same JSON structure with corrected verification_level and "
            . "sources. If it cannot be verified officially, mark it accordingly and "
            . "set unverified fields to null:\n"
            . json_encode($r->context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /** Appended to every prompt so the model returns the exact shape we validate. */
    private function schemaReminder(): string
    {
        return "IMPORTANT: The \"series\" array MUST be populated with the real "
            . "product series/models and their variants — do NOT return an empty "
            . "\"series\" array. Each variant MUST include at least one official "
            . "source URL. Put the manufacturer name on each variant if several "
            . "manufacturers are involved.\n\n"
            . "Respond with a single JSON object matching this shape exactly:\n"
            . '{"product_family":{"name":string,"normalized_name":string},'
            . '"manufacturer":{"name":string,"official_website":string|null,"country":string|null},'
            . '"series":[{"series_name":string,"official_product_name":string|null,'
            . '"official_page_url":string|null,"models":[{"model_number":string|null,'
            . '"body_material":string|null,"ball_material":string|null,"seat_material":string|null,'
            . '"port_type":string|null,"pieces":int|null,"operation_type":string|null,'
            . '"variants":[{"manufacturer_sku":string|null,"manufacturer_part_number":string|null,'
            . '"size":string|null,"dn_size":string|null,"connection":string|null,'
            . '"connection_standard":string|null,"pressure_rating":string|null,'
            . '"temperature_min":number|null,"temperature_max":number|null,"temperature_unit":string|null,'
            . '"approvals":[{"name":string,"code":string|null,"scope":string|null}],'
            . '"standards":[string],"verification_level":string,"availability_status":string,'
            . '"sources":[{"title":string,"url":string,"source_type":string,"is_official":bool,'
            . '"supports_fields":[string]}]}]}]}],"unverified_items":[],"warnings":[]}';
    }
}
