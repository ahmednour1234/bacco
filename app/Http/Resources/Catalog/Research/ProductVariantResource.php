<?php

namespace App\Http\Resources\Catalog\Research;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a product variant. Deliberately price-free — this module
 * has no price concept, so none is exposed.
 */
class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'                => $this->uuid,
            'manufacturer'        => $this->whenLoaded('manufacturer', fn () => $this->manufacturer?->name),
            'manufacturer_sku'    => $this->manufacturer_sku,
            'part_number'         => $this->manufacturer_part_number,
            'variant_name'        => $this->variant_name,
            'size'                => $this->whenLoaded('size', fn () => $this->size?->display_value),
            'connection'          => $this->whenLoaded('connectionType', fn () => $this->connectionType?->name),
            'pressure_rating'     => $this->whenLoaded('pressureRating', fn () => $this->pressureRating?->rating_name),
            'verification_level'  => $this->verification_level?->value,
            'verification_status' => $this->verification_status?->value,
            'availability_status' => $this->availability_status?->value,
            'approvals'           => $this->whenLoaded('approvals', fn () =>
                $this->approvals->map(fn ($a) => ['name' => $a->name, 'code' => $a->approval_code])),
        ];
    }
}
