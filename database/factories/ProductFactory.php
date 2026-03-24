<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'category_id'    => Category::factory(),
            'brand_id'       => Brand::factory(),
            'unit_id'        => Unit::factory(),
            'name'           => fake()->words(3, true),
            'sku'            => strtoupper(fake()->unique()->bothify('PRD-####-??')),
            'description'    => fake()->paragraph(),
            'specifications' => [
                'color'    => fake()->colorName(),
                'material' => fake()->word(),
            ],
            'active'         => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['active' => false]);
    }
}
