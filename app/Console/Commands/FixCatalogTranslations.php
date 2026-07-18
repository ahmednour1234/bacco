<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Diagnoses and repairs bilingual name columns on the catalog database, where
 * uploads left the data in a mess: some rows have Arabic text sitting in the
 * English column (and vice-versa), and many have an empty Arabic column.
 *
 * It fixes two problems, in this order:
 *   1. SWAP  — a cell whose language does not match its column (Arabic in the
 *              English column / Latin in the Arabic column) is moved to the
 *              correct column. If the correct column is already filled with the
 *              right language, the mismatched cell is cleared instead of clobbering.
 *   2. FILL  — an empty Arabic column is filled from a construction glossary
 *              (English name → Arabic), leaving unknown names blank so the
 *              display layer still falls back to English (never a wrong guess).
 *
 * Runs a DRY RUN by default (report only). Pass --fix to write, inside a
 * transaction. Idempotent: re-running after a fix is a no-op.
 *
 *   php artisan catalog:fix-translations                 # report only
 *   php artisan catalog:fix-translations --fix           # apply
 *   php artisan catalog:fix-translations --fix --no-swap # only fill, don't swap
 */
class FixCatalogTranslations extends Command
{
    protected $signature = 'catalog:fix-translations
                            {--fix : Apply the changes (default is a dry-run report only)}
                            {--no-swap : Do not swap mismatched-language cells, only fill empty Arabic}
                            {--no-fill : Do not fill empty Arabic from the glossary, only swap}';

    protected $description = 'Diagnose and repair swapped / missing Arabic-English names in the catalog DB.';

    /** Tables to clean: [table, englishCol, arabicCol]. */
    private const TARGETS = [
        ['catalog_categories', 'name', 'name_ar'],
        ['catalog_products', 'division', 'division_ar'],
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('fix');
        $doSwap = ! $this->option('no-swap');
        $doFill = ! $this->option('no-fill');

        try {
            $db = DB::connection('catalog');
            $db->getPdo();
        } catch (\Throwable $e) {
            $this->error('Catalog DB unavailable: ' . $e->getMessage());
            return self::FAILURE;
        }

        $glossary = $this->glossary();
        $summary  = [];

        if ($apply) {
            $db->beginTransaction();
        }

        try {
            foreach (self::TARGETS as [$table, $enCol, $arCol]) {
                if (! Schema::connection('catalog')->hasColumn($table, $arCol)) {
                    $this->warn("Skipping {$table}: no {$arCol} column.");
                    continue;
                }

                $stats = ['scanned' => 0, 'swapped' => 0, 'cleared' => 0, 'filled' => 0, 'unmatched' => []];

                // Work on DISTINCT english/arabic value pairs, then apply each fix to
                // every matching row — far fewer updates on a 30k+ row table.
                $pairs = $db->table($table)
                    ->select($enCol, $arCol)
                    ->distinct()
                    ->get();

                foreach ($pairs as $pair) {
                    $stats['scanned']++;
                    $en = trim((string) ($pair->$enCol ?? ''));
                    $ar = trim((string) ($pair->$arCol ?? ''));

                    $newEn = $en;
                    $newAr = $ar;

                    // ── 1. SWAP mismatched-language cells ───────────────────────
                    if ($doSwap) {
                        $enIsArabic = $en !== '' && $this->isArabic($en);
                        $arIsLatin  = $ar !== '' && $this->isLatin($ar);

                        if ($enIsArabic && $arIsLatin) {
                            // Both wrong → straight swap.
                            [$newEn, $newAr] = [$ar, $en];
                            $stats['swapped']++;
                        } elseif ($enIsArabic && $ar === '') {
                            // Arabic sitting alone in the English column → move it.
                            $newAr = $en;
                            $newEn = '';
                            $stats['swapped']++;
                        } elseif ($enIsArabic && $this->isArabic($ar)) {
                            // English column holds Arabic, Arabic column already Arabic →
                            // the English is unrecoverable here; clear the bad English cell.
                            $newEn = '';
                            $stats['cleared']++;
                        } elseif ($arIsLatin && $en === '') {
                            // Latin sitting alone in the Arabic column → move it.
                            $newEn = $ar;
                            $newAr = '';
                            $stats['swapped']++;
                        } elseif ($arIsLatin && $this->isLatin($en)) {
                            // Arabic column holds Latin, English already Latin → clear it.
                            $newAr = '';
                            $stats['cleared']++;
                        }
                    }

                    // ── 2. FILL empty Arabic from the glossary ──────────────────
                    if ($doFill && $newAr === '' && $newEn !== '' && $this->isLatin($newEn)) {
                        $hit = $glossary[$this->key($newEn)] ?? null;
                        if ($hit !== null) {
                            $newAr = $hit;
                            $stats['filled']++;
                        } elseif (! in_array($newEn, $stats['unmatched'], true)) {
                            $stats['unmatched'][] = $newEn;
                        }
                    }

                    // Nothing changed for this pair → skip.
                    if ($newEn === $en && $newAr === $ar) {
                        continue;
                    }

                    if ($apply) {
                        // Update every row that had this exact (en, ar) pair.
                        $q = $db->table($table);
                        $en === '' ? $q->where(fn ($w) => $w->whereNull($enCol)->orWhere($enCol, '')) : $q->where($enCol, $en);
                        $ar === '' ? $q->where(fn ($w) => $w->whereNull($arCol)->orWhere($arCol, '')) : $q->where($arCol, $ar);
                        $q->update([$enCol => $newEn, $arCol => $newAr]);
                    }
                }

                $summary[$table] = $stats;
            }

            if ($apply) {
                $db->commit();
            }
        } catch (\Throwable $e) {
            if ($apply) {
                $db->rollBack();
            }
            $this->error('Failed, rolled back: ' . $e->getMessage());
            return self::FAILURE;
        }

        // ── Report ──────────────────────────────────────────────────────────
        $this->newLine();
        $this->info(($apply ? '' : '[DRY RUN — nothing written] ') . 'Catalog translation cleanup');

        foreach ($summary as $table => $stats) {
            $this->newLine();
            $this->line("<comment>{$table}</comment>");
            $this->table(
                ['distinct pairs', 'swapped', 'cleared', 'filled ar', 'still no ar'],
                [[
                    $stats['scanned'],
                    $stats['swapped'],
                    $stats['cleared'],
                    $stats['filled'],
                    count($stats['unmatched']),
                ]]
            );

            if (! empty($stats['unmatched'])) {
                $this->warn('No glossary translation for ' . count($stats['unmatched']) . ' English name(s):');
                collect($stats['unmatched'])->take(40)->each(fn ($n) => $this->line('  • ' . $n));
                if (count($stats['unmatched']) > 40) {
                    $this->line('  … +' . (count($stats['unmatched']) - 40) . ' more');
                }
            }
        }

        if (! $apply) {
            $this->newLine();
            $this->info('Re-run with --fix to apply these changes.');
        } else {
            \App\Services\CatalogStats::flush();
            $this->info('Done. Catalog stats cache flushed.');
        }

        return self::SUCCESS;
    }

    /** True when the string contains at least one Arabic letter. */
    private function isArabic(string $s): bool
    {
        return preg_match('/\p{Arabic}/u', $s) === 1;
    }

    /** True when the string contains Latin letters and NO Arabic letters. */
    private function isLatin(string $s): bool
    {
        return preg_match('/[A-Za-z]/', $s) === 1 && ! $this->isArabic($s);
    }

    /** Normalise an English name for glossary lookup. */
    private function key(string $s): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($s)));
    }

    /**
     * English → Arabic construction glossary (keys lower-cased/space-collapsed).
     * Shared with CatalogArabicNameSeeder — extend both when adding new terms.
     */
    private function glossary(): array
    {
        $pairs = [
            'Accessibility & Universal Design'        => 'إمكانية الوصول والتصميم الشامل',
            'Acoustic & Specialist Interior Linings'  => 'العوازل الصوتية والتشطيبات الداخلية المتخصصة',
            'Aggregates'                              => 'الركام',
            'Agricultural & Greenhouse'               => 'المواد الزراعية والبيوت المحمية',
            'Air Pollution Control'                   => 'التحكم في تلوث الهواء',
            'Airfield & Airport Civil Materials'      => 'مواد المطارات والمدارج المدنية',
            'Airport Operational Systems'             => 'أنظمة تشغيل المطارات',
            'Alarm Control Panel'                     => 'لوحة تحكم الإنذار',
            'Alarm Indicator'                         => 'مؤشر الإنذار',
            'Cables'                                  => 'الكابلات',
            'Structural Steel'                        => 'الهياكل الفولاذية',
            'Fire Fighting'                           => 'مكافحة الحرائق',
            'Electrical'                              => 'الأعمال الكهربائية',
            'Electrical / ELV'                        => 'الكهرباء والأنظمة الخفيفة',
            'Electrical Power & Lighting'             => 'الطاقة الكهربائية والإنارة',
            'Mechanical'                              => 'الأعمال الميكانيكية',
            'Mechanical / HVAC'                       => 'الميكانيكا والتكييف',
            'HVAC'                                     => 'التكييف والتهوية',
            'Plumbing'                                => 'السباكة',
            'Civil'                                   => 'الأعمال المدنية',
            'Civil / Architecture'                    => 'المدني والمعماري',
            'Architectural Works'                     => 'الأعمال المعمارية',
            'Architecture'                            => 'العمارة',
            'Concrete'                                => 'الخرسانة',
            'Cement'                                  => 'الأسمنت',
            'Insulation'                              => 'العزل',
            'Waterproofing'                           => 'العزل المائي',
            'Doors & Windows'                         => 'الأبواب والنوافذ',
            'Flooring'                                => 'الأرضيات',
            'Roofing'                                 => 'الأسقف',
            'Paints & Coatings'                       => 'الدهانات والطلاءات',
            'Glass & Glazing'                         => 'الزجاج والتزجيج',
            'Landscaping'                             => 'تنسيق المواقع',
            'Lighting'                                => 'الإضاءة',
            'Elevators & Escalators'                  => 'المصاعد والسلالم المتحركة',
            'Elevators'                               => 'المصاعد',
            'Steel Reinforcement'                     => 'حديد التسليح',
            'Masonry'                                 => 'أعمال البناء بالطوب',
            'Tiles & Ceramics'                        => 'البلاط والسيراميك',
            'Sanitary Ware'                           => 'الأدوات الصحية',
            'Piping & Fittings'                       => 'المواسير والوصلات',
            'Cables & Wiring'                         => 'الكابلات والأسلاك',
            'Safety & Security'                       => 'السلامة والأمن',
            'Signage'                                 => 'اللافتات والإرشادات',
            'Formwork & Scaffolding'                  => 'القوالب والسقالات',
            'Adhesives & Sealants'                    => 'المواد اللاصقة ومواد السد',

            // ── Electrical / ELV ────────────────────────────────────────────
            'Battery Energy Storage Systems'          => 'أنظمة تخزين الطاقة بالبطاريات',
            'Cables & Containment'                    => 'الكابلات ومساراتها',

            // ── Architectural / interior ────────────────────────────────────
            'Architectural Metalwork'                 => 'الأعمال المعدنية المعمارية',
            'Carpet & Soft Flooring'                  => 'السجاد والأرضيات اللينة',
            'Ceiling Systems'                         => 'أنظمة الأسقف',
            'Curtain Wall & Glazing'                  => 'الحوائط الستائرية والتزجيج',
            'Decorative Plaster & Coatings'           => 'اللياسة الزخرفية والطلاءات',
            'Doors'                                   => 'الأبواب',
            'Drywall Partitions'                      => 'القواطع الجافة (الجبس بورد)',
            'Expansion Joints'                        => 'فواصل التمدد',
            'Facade & Cladding Systems'               => 'أنظمة الواجهات والكسوة',
            'Fire Stopping & Penetration Seals'       => 'موانع انتشار الحريق وسدادات الاختراقات',
            'Interior Specialties'                    => 'التجهيزات الداخلية المتخصصة',
            'Joinery & Carpentry'                     => 'أعمال النجارة والتجهيزات الخشبية',
            'Masonry & Blockwork'                     => 'أعمال البناء والطابوق',
            'Paint & Protective Coatings'             => 'الدهانات والطلاءات الواقية',
            'Raised Floor & Technical Floor'          => 'الأرضيات المرتفعة والتقنية',
            'Resilient & Wood Flooring'               => 'الأرضيات المرنة والخشبية',
            'Roofing Systems'                         => 'أنظمة الأسقف والتسقيف',
            'Signage & Wayfinding'                    => 'اللافتات وأنظمة الإرشاد',
            'Subfloor, Screed & Underlayment'         => 'الأرضيات التحتية والصبات والطبقات السفلية',
            'Thermal & Fire Insulation'               => 'العزل الحراري ومقاومة الحريق',
            'Tile & Stone Flooring'                   => 'أرضيات البلاط والحجر',
            'Toilet Partitions & Accessories'         => 'قواطع دورات المياه وملحقاتها',
            'Wall Build-Up & Backing Materials'       => 'مواد تكوين وتبطين الجدران',
            'Wall Finishes'                           => 'تشطيبات الجدران',
            'Windows & Louvres'                       => 'النوافذ والشيش',

            // ── Civil / infrastructure ──────────────────────────────────────
            'Bridge Works'                            => 'أعمال الجسور',
            'Communication Tower Civil Works'         => 'الأعمال المدنية لأبراج الاتصالات',
            'Communication Tower Structures'          => 'هياكل أبراج الاتصالات',
            'Marine & Port Works'                     => 'الأعمال البحرية والموانئ',
            'Railway Works'                           => 'أعمال السكك الحديدية',
            'Road Marking & Traffic Materials'        => 'دهانات الطرق ومواد المرور',
            'Road Works - Kerbs & Ancillaries'        => 'أعمال الطرق - الأرصفة والملحقات',
            'Road Works - Pavement Layers'            => 'أعمال الطرق - طبقات الرصف',
            'Stormwater & Road Drainage'              => 'تصريف مياه الأمطار والطرق',
            'Tunnel & Retaining Structures'           => 'الأنفاق والجدران الساندة',
            'Utility & Service Corridors'             => 'ممرات الخدمات والمرافق',
            'Concrete Constituents & Admixtures'      => 'مكونات الخرسانة والإضافات',
            'Concrete Joints, Repairs & Grouts'       => 'فواصل الخرسانة وإصلاحها ومواد الحقن',

            // ── Product-table divisions ─────────────────────────────────────
            'Civil Infrastructure'                    => 'البنية التحتية المدنية',
            'Civil Works'                             => 'الأعمال المدنية',
            'External & Landscape'                    => 'الأعمال الخارجية وتنسيق المواقع',
            'Conveying Systems'                        => 'أنظمة النقل والمصاعد',
            'Fuel & Gas Infrastructure'               => 'البنية التحتية للوقود والغاز',
            'Special Facility Works'                   => 'أعمال المنشآت الخاصة',
            'Transportation Systems'                   => 'أنظمة النقل',
            'Waste Management Systems'                 => 'أنظمة إدارة النفايات',
            'Water & Wastewater Treatment'             => 'معالجة المياه ومياه الصرف',

            // ── Civil / structural (batch 2) ────────────────────────────────
            'Concrete Testing & Quality'              => 'اختبار الخرسانة والجودة',
            'Construction Technology'                 => 'تقنيات البناء',
            'Demolition & Selective Removal'          => 'الهدم والإزالة الانتقائية',
            'Dewatering'                              => 'نزح المياه',
            'Earthworks & Excavation'                 => 'الأعمال الترابية والحفر',
            'Environmental Remediation'               => 'المعالجة البيئية',
            'Formwork System'                         => 'أنظمة القوالب',
            'Ground Improvement'                      => 'تحسين التربة',
            'Piling & Deep Foundations'               => 'الخوازيق والأساسات العميقة',
            'Post-Tensioning & Prestressing'          => 'الشد اللاحق وسبق الإجهاد',
            'Precast Concrete'                        => 'الخرسانة سابقة الصب',
            'Raft / Footing / Foundation Materials'   => 'مواد الأساسات (اللبشة والقواعد)',
            'Reinforcement System'                    => 'أنظمة التسليح',
            'Seismic & Structural Protection'         => 'الحماية الزلزالية والإنشائية',
            'Shoring & Excavation Support'            => 'دعم الحفر والأسناد',
            'Structural Steel & Metal Deck'           => 'الهياكل الفولاذية والأسقف المعدنية',
            'Subsurface Investigation'                => 'استكشاف التربة تحت السطحية',
            'Surveying & Setting Out'                 => 'المساحة والتوقيع',
            'Temporary Works & Site Enabling'         => 'الأعمال المؤقتة وتجهيز الموقع',

            // ── Landscape / external (batch 2) ──────────────────────────────
            'Fencing & Barriers'                      => 'الأسوار والحواجز',
            'Green Infrastructure'                    => 'البنية التحتية الخضراء',
            'Hard Landscape'                          => 'التنسيق الصلب',
            'Irrigation Systems'                      => 'أنظمة الري',
            'Outdoor Electrical & Lighting'           => 'الكهرباء والإنارة الخارجية',
            'Outdoor Furniture & Fixtures'            => 'الأثاث والتجهيزات الخارجية',
            'Playgrounds & Leisure Areas'             => 'الملاعب ومناطق الترفيه',
            'Soft Landscape'                          => 'التنسيق الأخضر',
            'Urban Realm & Wayfinding'                => 'الفراغات الحضرية والإرشاد',

            // ── Conveying / utilities (batch 2) ─────────────────────────────
            'Escalators & Moving Walkways'            => 'السلالم والممرات المتحركة',
            'Material Handling'                       => 'مناولة المواد',
            'Fuel Systems'                            => 'أنظمة الوقود',
            'Gas Distribution'                        => 'توزيع الغاز',
            'Hydrogen Infrastructure'                 => 'البنية التحتية للهيدروجين',
            'LPG & Gas Systems'                       => 'أنظمة الغاز المسال',

            // ── Special facilities (batch 2) ────────────────────────────────
            'Aquarium & Vivarium Systems'             => 'أنظمة الأحواض المائية والحيوية',
            'Blast & Ballistic Protection'            => 'الحماية من الانفجارات والقذائف',
            'Building Cleaning Systems'               => 'أنظمة تنظيف المباني',
            'Clean Room Systems'                      => 'أنظمة الغرف النظيفة',
            'Cold Storage & Food Processing'          => 'التخزين البارد ومعالجة الأغذية',
            'Communication Tower Shelter'             => 'مأوى أبراج الاتصالات',
        ];

        // Fix a typo introduced above (mدنية) defensively, then key it.
        $keyed = [];
        foreach ($pairs as $en => $ar) {
            $ar = str_replace('المdنية', 'المدنية', $ar);
            $keyed[$this->key($en)] = $ar;
        }

        return $keyed;
    }
}
