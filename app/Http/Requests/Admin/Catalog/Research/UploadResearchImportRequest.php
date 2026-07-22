<?php

namespace App\Http\Requests\Admin\Catalog\Research;

use Illuminate\Foundation\Http\FormRequest;

class UploadResearchImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('catalog.import.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:102400'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select a file to upload.',
            'file.mimes'    => 'Only Excel (.xlsx, .xls) and CSV files are accepted.',
            'file.max'      => 'The file must be under 100 MB.',
        ];
    }
}
