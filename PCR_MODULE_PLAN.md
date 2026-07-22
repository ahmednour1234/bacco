# Product Catalog Research & Excel Import Module — خطة التنفيذ

> Module بحثي فوق connection `catalog` الموجود. **لا أسعار. لا Cartesian product. كل Variant له Verification Level ومصدر.**
> يلتزم بكل قواعد المشروع: connection `catalog`، migrations تحت `database/migrations/catalog/`، Models/Services/Repositories/Jobs/Controllers/Requests بنفس المجلدات والأنماط، Blade + `layouts.admin-app`، حماية `middleware(['auth','employee'])`، DeepSeek من `config/services.php`.

## ⚠️ تصحيحات مبنية على فحص عميق للمشروع (مُلزِمة)
1. **لا `maatwebsite/excel`** (مذكور لكن غير مثبّت). سأقرأ/أكتب Excel بـ **`PhpOffice\PhpSpreadsheet` مباشرة** كما في `QuotationAiService` — لا facade Excel.
2. **لا Policies / Gates / Spatie / Events / Listeners / Notifications directories.** الصلاحيات = فحص `user_type` عبر middleware الموجود `employee`. سأضيف طبقة صلاحيات خفيفة عبر **Gate::define** داخل `AppServiceProvider` (النمط الوحيد المتاح) بدلاً من Policy classes، وأربط `catalog.*` بها. لا أخترع مجلدات غير موجودة.
3. **Models تمتد `App\Models\BaseModel`** + trait `HasPublicUuid` (uuid تلقائي، `getRouteKeyName='uuid'`, `scopeByUuid`)، `$guarded=['id']`، casts عبر method `casts()`. **لا** `$fillable` يدوي ولا توليد uuid يدوي — إلا لجداول connection catalog التي لا تمتد BaseModel (سأتبع نمط `CatalogProduct` الموجود: `$connection='catalog'` + uuid في `booted()`).
4. **Enums string-backed** مع `label()` و`values()`.
5. **Admin = Blade controller + Livewire مضمّن** (`IndexTable`, `Form`) للجداول التفاعلية؛ الصفحة `@extends('layouts.admin-app')`.
6. **Audit** = نموذج بسيط `catalog_audit_logs` + كتابة من الـServices (لا حزمة). i18n في `lang/en/app.php`+`lang/ar/app.php`.
7. **Notifications** عبر `NotificationService` الموجود عند اكتمال/فشل البحث.

---

## 1. الملفات المضافة (لا تعديل معماري — إضافات فقط)

### Config
- `config/catalog_research.php` (جديد) — batch sizes, refresh days, source domains, provider.
- تعديل `config/services.php` → إضافة مفاتيح deepseek الناقصة (`base_url`, `timeout`, `max_retries`, `rate_limit`) داخل مصفوفة `deepseek` الموجودة.
- تعديل `.env.example` → المتغيرات الجديدة (بدون قيم سرية).

### Migrations (تحت `database/migrations/catalog/` — connection `catalog`، مع حارس hasTable)
كل الجداول الـ29. أسماء صريحة، لا تضارب مع `catalog_products` الحالي.

### Enums (`app/Enums/Catalog/`)
`ResearchStatusEnum`, `ResearchScopeEnum`, `VerificationLevelEnum`, `VerificationStatusEnum`, `AvailabilityStatusEnum`, `ManufacturerTypeEnum`, `SourceTypeEnum`, `ResearchJobTypeEnum`, `ResearchJobStatusEnum`, `ImportRowStatusEnum`, `SourceEvidenceStatusEnum`, `ComponentTypeEnum`, `ReviewStatusEnum`, `CatalogImportStatusEnum` (للـcatalog_imports الجديد — منفصل عن الموجود).

### Models (`app/Models/Catalog/Research/`)
`CatalogImport` (research)، `CatalogImportRow`، `CatalogDivision`، `CatalogCategoryNode`، `ProductFamily`، `Manufacturer`، `ProductFamilyManufacturer`، `ProductSeries`، `ProductModel`، `ProductVariant`، `Material`، `PortType`، `ConnectionType`، `ConnectionStandard`، `ProductSize`، `PressureRating`، `Approval`، `Standard`، `Unit`، `SourceDocument`، `ProductSourceEvidence`، `ResearchJob`، `ResearchJobResult`، `AiProviderLog`، `ProductReviewQueue`، `ProductDuplicateCandidate`.
> كل model: `$connection='catalog'`, UUID في `booted()`, `SoftDeletes` حيث محدد, relations, casts.

### Repositories (`app/Repositories/Catalog/Research/`)
واحد لكل aggregate: Import, ProductFamily, Manufacturer, ProductVariant, ResearchJob, SourceDocument, ReviewQueue, Lookup (للـ dictionaries: materials/sizes/pressure...).

### Services (`app/Services/Catalog/Research/`)
- `ExcelImportService` — رفع، sheets، preview، mapping، تخزين rows.
- `ColumnMappingService` — حفظ/استرجاع mapping.
- `RowNormalizationService` — تقسيم multi-value → records، raw محفوظ.
- `NormalizationEngine` — size/pressure/connection normalization + `normalized_variant_key`.
- `DeduplicationEngine` — similarity + review candidates.
- `ProductFamilyService`، `ProductCatalogService`، `VerificationService`.
- `ResearchPlanService` — بناء الخطة + batching + progress + pause/resume/cancel + منع التكرار (lock).
- **DeepSeek layer** (`app/Services/Catalog/Research/DeepSeek/`):
  - `DeepSeekCatalogResearchService` (facade/orchestrator)
  - `DeepSeekClient` (HTTP، retry، rate limit، circuit breaker، logging → ai_provider_logs)
  - `AiResearchProvider` interface + `DeepSeekProvider` (قابل للاستبدال بمزود آخر)
  - `Prompts/ResearchPromptBuilder` (system prompts للمراحل الخمس)
  - `Dto/*` (RequestPayload, DiscoveredFamily/Series/Model/Variant/Source)
  - `ResearchResponseParser` + `ResearchResponseSchema` (JSON Schema validation)

### Jobs (`app/Jobs/Catalog/Research/`)
`ProcessCatalogResearchImportJob`, `ResearchProductFamilyJob`, `ResearchManufacturerProductsJob`, `ResearchProductSeriesJob`, `ResearchProductVariantsJob`, `VerifyProductSourceJob`, `ProcessResearchResultJob`, `RefreshProductCatalogJob`, `DetectProductDuplicatesJob`.

### Imports/Exports
- `app/Imports/Catalog/Research/SourceRowsImport.php`
- `app/Exports/Catalog/Research/ProductVariantsExport.php` (أعمدة بدون أسعار).

### Controllers (`app/Http/Controllers/Admin/Catalog/Research/`)
Imports, ProductFamilies, Manufacturers, ProductCatalog, ResearchJobs, ReviewQueue, SourceRegister, Exports.
Livewire tables/forms: `app/Livewire/Admin/Catalog/Research/` (IndexTable/Form لكل شاشة).
### API (`app/Http/Controllers/Api/Catalog/Research/`) + API Resources + FormRequests + response envelope موحّد (Trait `ApiResponse`).

### Authorization
`Gate::define('catalog.import.view', ...)` ... لكل صلاحية داخل `AppServiceProvider`، مربوطة بـ `user_type` (admin=كل شيء، employee=subset قابل للضبط من `config/catalog_research.php`). فحص عبر `$this->authorize()` في controllers و`@can` في blade.
### Requests (`app/Http/Requests/Admin/Catalog/Research/`).

### Views (`resources/views/admin/catalog/research/`)
imports/, families/, manufacturers/, products/, jobs/, review/, sources/ — Blade + `layouts.admin-app`، RTL. تعديل sidebar في `layouts/admin-app.blade.php` (إضافة مجموعة "Product Research").

### Console (`app/Console/Commands/Catalog/`)
`catalog:research-migrate` (يشغّل migrations/catalog)، `catalog:refresh-stale-products` (scheduler)، تسجيل schedule في `routes/console.php`.

### Seeders (`database/seeders/Catalog/`)
Units, Materials, PortTypes, ConnectionTypes, ConnectionStandards, PressureRatings, Approvals, Standards (قيم مرجعية من المتطلبات).

### Permissions
مصفوفة صلاحيات `catalog.*` — تُطبَّق عبر Policies + Gates (بما يتوافق مع نظام user_type الحالي). ملف `config/catalog_research.php['permissions']` + `CatalogResearchPolicy` gates.

### Tests (`tests/Feature/Catalog/Research/` + `tests/Unit/Catalog/Research/`)
كل السيناريوهات المطلوبة + `FakeDeepSeekProvider` (لا API حقيقي).

---

## 2. ERD (مختصر — العلاقات الأساسية)

```
catalog_imports 1─* catalog_import_rows ─(0..1)→ product_families
catalog_divisions 1─* catalog_categories (self parent_id) 1─* product_families
product_families *─* manufacturers  (product_family_manufacturers)
product_families 1─* product_series 1─* product_models 1─* product_variants
manufacturers 1─* product_series / product_models / product_variants
product_models *─* materials (product_model_materials, component_type)
product_models → port_types, operation_type
product_variants → product_sizes, connection_types, connection_standards,
                   pressure_ratings, units, finishes
product_variants *─* approvals (product_variant_approvals + source_id)
product_variants *─* standards  (product_variant_standards + source_id)
source_documents 1─* product_source_evidence →(model|variant)
research_jobs 1─* research_job_results
ai_provider_logs (standalone)
product_review_queue (polymorphic reviewable)
product_duplicate_candidates (variant×variant)
```

## 3. مسؤوليات الجداول
مطابقة لوصف المتطلبات (29 جدول) — كل جدول له مسؤولية واحدة، multi-value → pivot، raw محفوظ، كل معلومة حساسة ← source.

## 4. Workflow: Excel → Verified Variant
1. رفع ملف → `catalog_imports` (status=uploaded) + تخزين الأصل.
2. قراءة sheets + preview 20 صف.
3. Column mapping (يُحفظ لإعادة الاستخدام).
4. Process → كل صف = `catalog_import_row` (row_hash، duplicate detection، normalized_row).
5. كل صف → `product_family` (research_status=not_started). **لا variants الآن.**
6. تقرير الاستيراد (totals/duplicates/failed/missing description/ready/review).
7. بدء البحث → `ResearchPlanService` يبني خطة، يقسّم manufacturers batches → Queue.
8. المراحل: discover manufacturers → series/models → variants → verify → dedup.
9. كل نتيجة تمر بـ JSON Schema → parser → transaction → source evidence.
10. verification_level يُحسب برمجيًا؛ verified يتطلب مصدر رسمي؛ distributor_only=partial؛ بلا URL → review queue.

## 5. DeepSeek Queue Architecture
`ResearchProductFamilyJob` (خطة) → dispatch batches → كل job يستدعي `DeepSeekProvider` عبر `DeepSeekClient` (retry/rate-limit/circuit-breaker، log إلى ai_provider_logs بدون secrets) → `ProcessResearchResultJob` (validate→persist→dedup). Progress% على research_job. Pause/Resume/Cancel عبر حالة + منع تكرار عبر lock (`Cache::lock("family-research:{id}")`). Idempotency عبر normalized_variant_key + firstOrCreate.

## 6. Phases (ترتيب التنفيذ)
- **P1** Schema + Models + Enums + Relationships + Seeders + migrate command.
- **P2** Excel Import + Preview + Mapping + Normalization + import report.
- **P3** DeepSeek Client + Provider + DTOs + Prompts + JSON Schema + Parser.
- **P4** Queues + Jobs + Research Plan + Progress + Pause/Resume/Retry.
- **P5** Product Catalog + Sources + Verification + Deduplication.
- **P6** Admin Dashboard + Review Queue + sidebar + API + Policies.
- **P7** Excel Export + Reporting.
- **P8** Tests + Security + Perf (indexes) + docs.

## 7. المخاطر والحلول
| خطر | حل |
|---|---|
| تضارب أسماء مع catalog module الحالي | namespace `Research` + جداول بأسماء صريحة، connection catalog، حارس hasTable |
| كسر migrations الحالية | migrations منفصلة + `catalog:research-migrate` command، graceful |
| Hallucination | القواعد الـ15 مطبقة في الكود (verification service) لا في prompt فقط |
| Cartesian product | لا توليد تركيبات؛ variant يُنشأ فقط من نتيجة بحث بمصدر |
| مفاتيح سرية في git | env فقط، ai_provider_logs بدون Authorization header |
| أحجام كبيرة (مئات الآلاف) | indexes + chunk + normalized_variant_key unique |
| مزود AI بديل مستقبلًا | interface `AiResearchProvider` |

## قيود مطبّقة
لا أسعار · لا منتجات افتراضية · لا Cartesian · DeepSeek ليس مصدرًا نهائيًا · لا اختراع SKU/Approval · كل variant له verification_level · raw محفوظ · صف DB واحد لكل variant · Queue للبحث · Transactions · Idempotency · Indexes.
