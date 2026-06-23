<?php

return [
    'title'              => 'Upload Catalog File',
    'catalog_label'      => 'Catalog',
    'catalog_hint'       => '(optional — auto-created from filename if left blank)',
    'auto_create'        => '— Auto-create from filename —',
    'files_label'        => 'Excel / CSV files',
    'drop_zone'          => 'Click or drag & drop your files here',
    'drop_hint'          => 'xlsx, xls, csv — up to 7 files, max 100 MB each',
    'expected_format'    => 'Expected Excel format:',
    'heading_row'        => 'Heading row must be on :row',
    'required_columns'   => 'Required columns:',
    'data_starts'        => 'Data starts on :row',
    'row'                => 'row :number',
    'cancel'             => 'Cancel',
    'submit'             => 'Upload & Queue',
    'oversize_warning'   => 'One or more files exceed 100 MB. Please reduce the file size before uploading.',

    // Column labels (English headers accepted in the Excel file)
    'columns' => [
        'qimta_code'       => 'Qimta Code',
        'division'         => 'Division',
        'category'         => 'Category',
        'item_description' => 'Item Description',
        'sub_type'         => 'Sub-Type',
        'product_name'     => 'Product Name',
        'type_of_material' => 'Type of Material',
        'size'             => 'Size',
        'unit'             => 'Unit',
        'lead_time'        => 'Lead Time',
    ],
];
