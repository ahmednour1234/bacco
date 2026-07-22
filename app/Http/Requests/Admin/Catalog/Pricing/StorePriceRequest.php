<?php

namespace App\Http\Requests\Admin\Catalog\Pricing;

use App\Enums\Catalog\Pricing\PriceSourceEnum;
use App\Enums\Catalog\Pricing\PriceTierEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('catalog.price.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'supplier_id'    => ['nullable', 'integer', 'exists:catalog.catalog_suppliers,id'],
            'price'          => ['required', 'numeric', 'min:0'],
            'currency'       => ['nullable', 'string', 'size:3'],
            'price_tier'     => ['required', Rule::in(PriceTierEnum::values())],
            'source'         => ['required', Rule::in(PriceSourceEnum::values())],
            'min_quantity'   => ['nullable', 'integer', 'min:1'],
            'max_quantity'   => ['nullable', 'integer', 'gte:min_quantity'],
            'price_unit'     => ['nullable', 'string', 'max:40'],
            'source_url'     => ['nullable', 'url', 'max:2048'],
            'valid_from'     => ['nullable', 'date'],
            'valid_to'       => ['nullable', 'date', 'after_or_equal:valid_from'],
            'lead_time_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'notes'          => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * A quotable price must be traceable. Supplier quotes and catalog prices
     * therefore need either a named supplier or a source URL — otherwise the
     * number has no provenance and cannot be defended to a customer.
     */
    public function after(): array
    {
        return [
            function ($validator) {
                $source = $this->input('source');
                $needsProvenance = in_array($source, [
                    PriceSourceEnum::SupplierQuote->value,
                    PriceSourceEnum::CatalogPdf->value,
                ], true);

                if ($needsProvenance && ! $this->filled('supplier_id') && ! $this->filled('source_url')) {
                    $validator->errors()->add(
                        'source_url',
                        'A supplier quote or catalog price needs either a supplier or a source URL.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'price.required' => 'Enter the price.',
            'price.min'      => 'The price cannot be negative.',
        ];
    }
}
