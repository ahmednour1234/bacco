<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        $units = [
            ['name' => 'Piece',        'symbol' => 'pcs'],
            ['name' => 'Kilogram',     'symbol' => 'kg'],
            ['name' => 'Ton',          'symbol' => 'ton'],
            ['name' => 'Meter',        'symbol' => 'm'],
            ['name' => 'Square Meter', 'symbol' => 'm2'],
            ['name' => 'Cubic Meter',  'symbol' => 'm3'],
            ['name' => 'Liter',        'symbol' => 'L'],
            ['name' => 'Box',          'symbol' => 'box'],
        ];

        $unit = fake()->unique()->randomElement($units);

        return [
            'name'   => $unit['name'],
            'symbol' => $unit['symbol'],
            'active' => true,
        ];
    }
}
