<?php

namespace App\Http\Requests\Admin\Catalog\Research;

use Illuminate\Foundation\Http\FormRequest;

class MapColumnsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('catalog.import.process') ?? false;
    }

    public function rules(): array
    {
        return [
            'sheet'      => ['required', 'string', 'max:255'],
            'header_row' => ['required', 'integer', 'min:1', 'max:50'],
            // mapping: header => target field (strings)
            'mapping'    => ['required', 'array', 'min:1'],
            'mapping.*'  => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Item Description must be mapped — without it there is nothing to build a
     * Product Family from, and the import would fail during processing.
     */
    public function after(): array
    {
        return [
            function ($validator) {
                $targets = array_values(array_filter((array) $this->input('mapping', [])));
                if (! in_array('item_description', $targets, true)) {
                    $validator->errors()->add(
                        'mapping',
                        'You must map a column to "Item Description" before importing.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'mapping.required' => 'Please map at least one column, including Item Description.',
        ];
    }
}
