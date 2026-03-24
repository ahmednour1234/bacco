<?php

namespace Database\Factories;

use App\Enums\QuotationItemStatusEnum;
use App\Models\Product;
use App\Models\QuotationItem;
use App\Models\QuotationRequest;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuotationItem>
 */
class QuotationItemFactory extends Factory
{
    protected $model = QuotationItem::class;

    public function definition(): array
    {
        return [
            'quotation_request_id' => QuotationRequest::factory(),
            'product_id'           => Product::factory(),
            'description'          => fake()->sentence(),
            'quantity'             => fake()->randomFloat(3, 1, 100),
            'unit_id'              => Unit::factory(),
            'status'               => QuotationItemStatusEnum::Pending->value,
            'notes'                => fake()->optional()->sentence(),
        ];
    }

    public function sourced(): static
    {
        return $this->state(['status' => QuotationItemStatusEnum::Sourced->value]);
    }
}
