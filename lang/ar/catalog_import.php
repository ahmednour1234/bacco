<?php

return [
    'title'              => 'رفع ملف الكتالوج',
    'catalog_label'      => 'الكتالوج',
    'catalog_hint'       => '(اختياري — يتم إنشاؤه تلقائيًا من اسم الملف إذا تُرك فارغًا)',
    'auto_create'        => '— إنشاء تلقائي من اسم الملف —',
    'files_label'        => 'ملفات Excel / CSV',
    'drop_zone'          => 'اضغط أو اسحب وأفلت ملفاتك هنا',
    'drop_hint'          => 'xlsx، xls، csv — حتى 7 ملفات، بحد أقصى 100 ميجابايت لكل ملف',
    'expected_format'    => 'تنسيق ملف Excel المتوقع:',
    'heading_row'        => 'صف العناوين يجب أن يكون في :row',
    'required_columns'   => 'الأعمدة المطلوبة:',
    'data_starts'        => 'تبدأ البيانات من :row',
    'row'                => 'الصف :number',
    'cancel'             => 'إلغاء',
    'submit'             => 'رفع وإضافة للقائمة',
    'oversize_warning'   => 'يتجاوز ملف واحد أو أكثر حجم 100 ميجابايت. يرجى تقليل حجم الملف قبل الرفع.',

    // أسماء الأعمدة العربية المقبولة في ملف Excel
    'columns' => [
        'qimta_code'       => 'كود قمتا',
        'division'         => 'القسم',
        'category'         => 'الفئة',
        'item_description' => 'وصف الصنف',
        'sub_type'         => 'النوع الفرعي',
        'product_name'     => 'اسم المنتج',
        'type_of_material' => 'نوع المادة',
        'size'             => 'الحجم',
        'unit'             => 'الوحدة',
        'lead_time'        => 'مدة التوريد',
    ],
];
