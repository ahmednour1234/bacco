<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryBrandSeeder extends Seeder
{

    public function run(): void
    {
        // ── Categories ─────────────────────────────────────────────────────────
        $parentCategories = [
            'Electrical Equipment',
            'Mechanical Parts',
            'Safety & PPE',
            'Pipes & Fittings',
            'Structural Steel',
            'Tools & Machinery',
        ];

        foreach ($parentCategories as $name) {
            $parent = Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'active' => true]
            );

            // Add a few child categories per parent
            $children = match ($name) {
                'Electrical Equipment' => ['Cables & Wires', 'Switchgear', 'Lighting'],
                'Mechanical Parts'     => ['Bearings', 'Gears', 'Seals'],
                'Safety & PPE'         => ['Helmets', 'Gloves', 'Safety Shoes'],
                'Pipes & Fittings'     => ['Steel Pipes', 'PVC Pipes', 'Flanges'],
                'Structural Steel'     => ['I-Beams', 'Angle Iron', 'Hollow Sections'],
                'Tools & Machinery'    => ['Power Tools', 'Hand Tools', 'Measuring Tools'],
                default                => [],
            };

            foreach ($children as $childName) {
                Category::firstOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'name'      => $childName,
                        'parent_id' => $parent->id,
                        'active'    => true,
                    ]
                );
            }
        }

        // ── Brands ─────────────────────────────────────────────────────────────
        $brands = [
            'Siemens', 'ABB', 'Schneider Electric', '3M', 'Honeywell',
            'Parker', 'SKF', 'Bosch', 'Hilti', 'Legrand',
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(['name' => $brand], ['active' => true]);
        }
    }
}
