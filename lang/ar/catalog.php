<?php

return [
    'title'           => 'كتالوج مواد البناء — :products منتج · تسعير BOQ | كيمتا',
    'breadcrumb_home' => 'الرئيسية',
    'breadcrumb_catalog' => 'الكتالوج',

    'header' => [
        'h1'      => 'تصفح كتالوج كيمتا للإنشاءات',
        'subtitle' => 'استكشف قاعدة البيانات الأكثر دقة في المجال. تصفح :divisions قسم، :categories فئة، :items بند، و:products منتج موثق.',
    ],

    'search_placeholder' => 'ابحث بالقسم أو الكلمة المفتاحية...',

    'info' => [
        'structure_title' => 'هيكل الكتالوج العام',
        'structure_body'  => 'يتبع التسلسل الهرمي التقني المعايير الدولية للإنشاءات. تنقل عبر مراكز الأقسام ثم مراكز الفئات وصولاً إلى صفحات توصيف البنود التقنية.',
        'note_label'      => 'ملاحظة:',
        'note_body'       => 'بيانات التسعير المباشر وأكواد SKU الخاصة بالمصنعين متاحة فقط للحسابات المؤسسية المسجلة.',
        'note_signup'     => 'سجّل للوصول إلى الأسعار الموثقة.',
    ],

    'stats' => [
        'divisions' => 'أقسام',
        'categories' => 'فئات',
        'items'     => 'بنود',
        'products'  => 'منتج موثق',
    ],

    'card' => [
        'products'       => 'منتج',
        'items'          => 'بند',
        'cats'           => 'فئة',
        'browse_division' => 'تصفح القسم',
    ],

    'empty' => [
        'title' => 'لا توجد بيانات في الكتالوج بعد',
        'body'  => 'استورد أول ملف كتالوج للبدء.',
    ],

    // ── Item-family (item_description) translations ──────────────────────────
    // Keyed by the exact English item_description from the catalog DB.
    // Any term not listed here falls back to its English text in the view.
    'items' => [
        'Accessible Door Operator' => 'مشغّل الأبواب لذوي الاحتياجات',
        'Handrail Braille Plate'   => 'لوحة برايل لدرابزين اليد',
        'Induction Loop System'    => 'نظام الحلقة الحثّية للصم',
        'Wheelchair Platform Lift' => 'مصعد منصة كرسي الإعاقة',
        'Tactile Warning Strip'    => 'شريط التحذير اللمسي للمكفوفين',
        'Lowered Counter Unit'     => 'وحدة الكاونتر المنخفض',
        'Contrasting Nosing Strip' => 'شريط حافة الدرج المتباين',
    ],

    // ── Division translations ────────────────────────────────────────────────
    // Keyed by the exact English division value from the catalog DB.
    // Any term not listed here falls back to its English text in the view.
    'divisions' => [
        'Electrical Power & Lighting' => 'الطاقة الكهربائية والإنارة',
    ],

    // ── Category translations ────────────────────────────────────────────────
    // Keyed by the exact English category name from the catalog DB.
    'categories' => [],
];
