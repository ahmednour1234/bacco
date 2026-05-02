<?php

namespace App\Http\Requests\Admin\Catalog;

use Illuminate\Foundation\Http\FormRequest;

class UploadCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // already guarded by route middleware
    }

    public function rules(): array
    {
        return [
            // catalog_id is optional — if omitted a catalog is auto-created from the filename
            'catalog_id' => ['nullable', 'integer', 'min:1'],
            'files'      => ['required', 'array', 'min:1', 'max:7'],
            'files.*'    => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:102400', // 100 MB per file
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'files.max'     => 'You may upload a maximum of 7 files per batch.',
            'files.*.mimes' => 'Only Excel (.xlsx, .xls) and CSV files are accepted.',
            'files.*.max'   => 'Each file must be under 100 MB.',
        ];
    }
}
