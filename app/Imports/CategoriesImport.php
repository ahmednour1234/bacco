<?php

namespace App\Imports;

use App\Models\Category;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CategoriesImport implements ToModel, WithHeadingRow, WithValidation
{
    public int $imported = 0;

    public function model(array $row): ?Category
    {
        $name = trim($row['name'] ?? '');
        if ($name === '') {
            return null;
        }

        $slug = trim($row['slug'] ?? '');
        if ($slug === '') {
            $slug = Str::slug($name);
        }

        // Ensure slug uniqueness
        $originalSlug = $slug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $parentId = null;
        $parentName = trim($row['parent'] ?? '');
        if ($parentName !== '') {
            $parent = Category::where('name', $parentName)->first();
            $parentId = $parent?->id;
        }

        $active = true;
        if (isset($row['active'])) {
            $val = strtolower(trim((string) $row['active']));
            $active = in_array($val, ['1', 'yes', 'true', 'نعم'], true);
        }

        $this->imported++;

        return new Category([
            'name'        => $name,
            'slug'        => $slug,
            'parent_id'   => $parentId,
            'description' => trim($row['description'] ?? '') ?: null,
            'active'      => $active,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
