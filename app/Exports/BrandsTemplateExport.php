<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BrandsTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function headings(): array
    {
        return ['name', 'description', 'active'];
    }

    public function array(): array
    {
        return [
            ['Philips', 'Dutch lighting brand', 'yes'],
            ['Osram', 'German lighting brand', 'yes'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
