<?php

namespace App\Imports;

use App\Models\Brand;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BrandsImport implements ToModel, WithHeadingRow, WithValidation
{
    public int $imported = 0;

    public function model(array $row): ?Brand
    {
        $name = trim($row['name'] ?? '');
        if ($name === '') {
            return null;
        }

        // Skip if brand already exists
        if (Brand::where('name', $name)->exists()) {
            return null;
        }

        $active = true;
        if (isset($row['active'])) {
            $val = strtolower(trim((string) $row['active']));
            $active = in_array($val, ['1', 'yes', 'true', 'نعم'], true);
        }

        $this->imported++;

        return new Brand([
            'name'        => $name,
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
