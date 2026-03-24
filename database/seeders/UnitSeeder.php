<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{

    public function run(): void
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
            ['name' => 'Set',          'symbol' => 'set'],
            ['name' => 'Roll',         'symbol' => 'roll'],
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['symbol' => $unit['symbol']], array_merge($unit, ['active' => true]));
        }
    }
}
