<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Backfills the Arabic label columns on the catalog database so category and
 * division cards render in Arabic when the site locale is `ar`.
 *
 * Both the catalog index and welcome page already prefer the Arabic value and
 * fall back to English when it is blank (see CatalogController). The English
 * names were showing because `catalog_categories.name_ar` / `catalog_products.
 * division_ar` were empty — this seeder fills them from a curated construction
 * glossary, leaving any unmapped name untouched (it still falls back to EN).
 *
 * Idempotent: only rows whose Arabic column is NULL/'' are updated, so re-running
 * never overwrites a translation an admin has already entered by hand.
 */
class CatalogArabicNameSeeder extends Seeder
{
    public function run(): void
    {
        $connection = 'catalog';

        try {
            $db = DB::connection($connection);
            $db->getPdo(); // force a real connection so we fail fast if it is down
        } catch (\Throwable $e) {
            $this->command->warn('Catalog DB unavailable — skipping Arabic name backfill.');
            return;
        }

        $map = $this->glossary();

        $categories = $this->backfill($db, 'catalog_categories', 'name', 'name_ar', $map);
        $divisions  = Schema::connection($connection)->hasColumn('catalog_products', 'division_ar')
            ? $this->backfill($db, 'catalog_products', 'division', 'division_ar', $map)
            : 0;

        $this->command->info("Arabic backfill: {$categories} categories, {$divisions} division rows updated.");

        $missing = $this->reportMissing($db);
        if ($missing->isNotEmpty()) {
            $this->command->warn('No Arabic translation for ' . $missing->count() . ' category name(s):');
            $missing->take(40)->each(fn ($n) => $this->command->line('  • ' . $n));
        }
    }

    /**
     * Update `$arCol` for every row where the English `$enCol` has a glossary
     * match and the Arabic column is still empty.
     */
    private function backfill(\Illuminate\Database\Connection $db, string $table, string $enCol, string $arCol, array $map): int
    {
        $updated = 0;

        $db->table($table)
            ->whereNotNull($enCol)
            ->where($enCol, '!=', '')
            ->where(function ($q) use ($arCol) {
                $q->whereNull($arCol)->orWhere($arCol, '');
            })
            ->select($enCol)
            ->distinct()
            ->orderBy($enCol)
            ->each(function ($row) use ($db, $table, $enCol, $arCol, $map, &$updated) {
                $en = trim((string) $row->$enCol);
                $ar = $map[$this->key($en)] ?? null;
                if ($ar === null) {
                    return; // leave blank → CatalogController falls back to EN
                }
                $updated += $db->table($table)
                    ->where($enCol, $en)
                    ->where(fn ($q) => $q->whereNull($arCol)->orWhere($arCol, ''))
                    ->update([$arCol => $ar]);
            });

        return $updated;
    }

    /** Distinct English category names that still have no glossary entry. */
    private function reportMissing(\Illuminate\Database\Connection $db)
    {
        $map = $this->glossary();

        return $db->table('catalog_categories')
            ->whereNotNull('name')->where('name', '!=', '')
            ->where(fn ($q) => $q->whereNull('name_ar')->orWhere('name_ar', ''))
            ->distinct()->orderBy('name')->pluck('name')
            ->filter(fn ($n) => !isset($map[$this->key($n)]))
            ->values();
    }

    /** Normalise a name for case/space-insensitive lookup. */
    private function key(string $name): string
    {
        return mb_strtolower(preg_replace('/\s+/', ' ', trim($name)));
    }

    /**
     * English → Arabic construction glossary. Keys are lower-cased/space-collapsed.
     * Extend this list as new categories appear (see the "missing" warning above).
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
            'Structural Steel'                        => 'الهياكل الفولاذية',
            'Fire Fighting'                           => 'مكافحة الحرائق',
            'Electrical'                              => 'الأعمال الكهربائية',
            'Electrical / ELV'                        => 'الكهرباء والأنظمة الخفيفة',
            'Mechanical'                              => 'الأعمال الميكانيكية',
            'Mechanical / HVAC'                       => 'الميكانيكا والتكييف',
            'HVAC'                                     => 'التكييف والتهوية',
            'Plumbing'                                => 'السباكة',
            'Civil'                                   => 'الأعمال المدنية',
            'Civil / Architecture'                    => 'المدني والمعماري',
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
        ];

        $keyed = [];
        foreach ($pairs as $en => $ar) {
            $keyed[$this->key($en)] = $ar;
        }

        return $keyed;
    }
}
