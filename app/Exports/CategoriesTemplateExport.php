<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CategoriesTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['name', 'slug', 'parent', 'description', 'active'];
    }

    public function array(): array
    {
        return [
            ['Lighting', 'lighting', '', 'All lighting products', 'yes'],
            ['LED Panels', 'led-panels', 'Lighting', 'LED panel lights', 'yes'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
