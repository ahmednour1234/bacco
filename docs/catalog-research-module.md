# Product Catalog Research & Excel Import Module

A research layer over the dedicated `catalog` database connection. It ingests
Qimta-style workbooks, turns each row into a **Product Family**, then discovers
**real, source-backed product variants** via a swappable AI provider (DeepSeek
by default). It never stores prices and never invents products.

## Hard rules (enforced in code, not just prompts)

- **No prices** — no cost/selling/currency/discount/tax/supplier/market columns exist anywhere.
- **No cartesian products** — a variant is only created from a real, documented result. Size × Connection × Pressure is never expanded.
- **Every variant has a `verification_level`** and, to be *verified*, an official manufacturer source.
- **DeepSeek is never a final source.** Official manufacturer page/PDF/datasheet/certificate is the authority.
- **UL 258 ≠ UL 842** — approvals are keyed by issuing body + code, never conflated.
- **Raw Excel rows are never deleted**; one variant = one DB row (no multi-value cells).
- Research runs on a **queue** with pause/resume/cancel/retry and is **idempotent** (`normalized_variant_key`).

## Layout

| Concern | Location |
|---|---|
| Migrations (32 tables, `catalog` conn) | `database/migrations/catalog/2026_07_22_*` |
| Enums | `app/Enums/Catalog/Research/` |
| Models | `app/Models/Catalog/Research/` |
| Repositories | `app/Repositories/Catalog/Research/` |
| Services | `app/Services/Catalog/Research/` |
| DeepSeek client/provider/schema | `app/Services/Catalog/Research/DeepSeek/` |
| Jobs | `app/Jobs/Catalog/Research/` |
| Admin controllers | `app/Http/Controllers/Admin/Catalog/Research/` |
| API controllers | `app/Http/Controllers/Api/Catalog/Research/` |
| Export | `app/Exports/Catalog/Research/ProductVariantsExport.php` |
| Views | `resources/views/admin/catalog/research/` |
| Config | `config/catalog_research.php` (+ `config/services.php['deepseek']`) |
| Gates + bindings | `app/Providers/CatalogResearchServiceProvider.php` |

## Setup

```bash
# 1. Configure the catalog DB + DeepSeek keys in .env (see .env.example).
# 2. Migrate + seed the module (runs on the `catalog` connection):
php artisan catalog:research-migrate --seed
# 3. Run the queue worker for the research queue:
php artisan queue:work --queue=catalog-research
```

## Workflow

1. **Upload** a workbook (XLSX/XLS/CSV) → stored, sheets read.
2. **Map columns** (saved for reuse) and preview 20 rows.
3. **Process**: each row → one raw `catalog_import_rows` (hashed for dedup) + one
   `product_families` (find-or-create) + manufacturer links. **No variants yet.**
4. **Import report**: total / imported / duplicate / failed / missing-description
   / ready-for-research / requiring-review.
5. **Start research** on a family → `ResearchPlanService` builds a plan and
   dispatches staged jobs (discover manufacturers → series → variants → verify →
   dedup) through the queue, with a per-family lock preventing concurrent runs.
6. Each AI response is **JSON-schema validated**, then persisted transactionally;
   `VerificationService` computes each variant's level/status from its sources.
7. **Export** the (filtered) catalog to Excel — one variant per row, no prices.

## Swapping the AI provider

Implement `App\Services\Catalog\Research\DeepSeek\Contracts\AiResearchProvider`
and set `CATALOG_RESEARCH_PROVIDER`. Tests use the built-in `fake` provider
(`FakeResearchProvider`) — no network calls are ever made.

## Permissions

`catalog.*` Gate abilities (see `config/catalog_research.php`), resolved against
`user_type`: admins pass everything, employees get the configured subset.

## Tests

```bash
php artisan test tests/Feature/Catalog/Research
```

Covers Excel import/normalization/dedup, DeepSeek response parsing + JSON-schema
validation, the UL 258/842 distinction, the full research pipeline (official →
verified, distributor → not verified), idempotency, pause/cancel, and a
price-free export. All AI calls use the fake provider.
