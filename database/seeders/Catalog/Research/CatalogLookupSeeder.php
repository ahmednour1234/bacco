<?php

namespace Database\Seeders\Catalog\Research;

use App\Models\Catalog\Research\Approval;
use App\Models\Catalog\Research\ConnectionStandard;
use App\Models\Catalog\Research\ConnectionType;
use App\Models\Catalog\Research\Material;
use App\Models\Catalog\Research\PortType;
use App\Models\Catalog\Research\PressureRating;
use App\Models\Catalog\Research\Standard;
use App\Models\Catalog\Research\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the reference dictionaries for the research catalog module.
 * Idempotent: every entry is upserted by its normalized/unique key, so it is
 * safe to run repeatedly and against a pre-existing catalog database.
 *
 * No pricing data is ever seeded — this module has no price concept.
 */
class CatalogLookupSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUnits();
        $this->seedMaterials();
        $this->seedPortTypes();
        $this->seedConnectionTypes();
        $this->seedConnectionStandards();
        $this->seedPressureRatings();
        $this->seedApprovals();
        $this->seedStandards();
    }

    private function norm(string $value): string
    {
        return Str::of($value)->lower()->squish()->value();
    }

    private function seedUnits(): void
    {
        $units = [
            ['Each', 'EA', 'count'],
            ['Piece', 'PC', 'count'],
            ['Set', 'SET', 'count'],
            ['Meter', 'M', 'length'],
            ['Roll', 'ROLL', 'count'],
            ['Box', 'BOX', 'count'],
        ];

        foreach ($units as [$name, $code, $type]) {
            Unit::updateOrCreate(['code' => $code], ['name' => $name, 'type' => $type]);
        }
    }

    private function seedMaterials(): void
    {
        // [name, category, standard_designation, aliases]
        $materials = [
            ['Brass', 'metal', null, ['brass']],
            ['DZR Brass', 'metal', null, ['dzr brass', 'dezincification resistant brass']],
            ['Lead-Free Brass', 'metal', null, ['lead free brass', 'lead-free brass']],
            ['Bronze', 'metal', null, ['bronze']],
            ['Bronze ASTM B584 C84400', 'metal', 'ASTM B584 C84400', ['c84400', 'astm b584 c84400']],
            ['Stainless Steel 316', 'metal', 'SS 316', ['ss316', 'stainless steel 316', '316 ss']],
            ['Chrome-Plated Brass', 'metal', null, ['chrome plated brass', 'chrome-plated brass']],
            ['PTFE', 'polymer', null, ['ptfe', 'teflon']],
            ['Reinforced PTFE', 'polymer', null, ['reinforced ptfe', 'rptfe']],
        ];

        foreach ($materials as [$name, $category, $std, $aliases]) {
            Material::updateOrCreate(
                ['normalized_name' => $this->norm($name)],
                [
                    'name'                 => $name,
                    'material_category'    => $category,
                    'standard_designation' => $std,
                    'aliases'              => $aliases,
                ]
            );
        }
    }

    private function seedPortTypes(): void
    {
        $ports = [
            'Full Port', 'Standard Port', 'Reduced Port', 'Conventional Port',
            'Three-Way', 'L-Port', 'T-Port',
        ];

        foreach ($ports as $name) {
            PortType::updateOrCreate(
                ['normalized_name' => $this->norm($name)],
                ['name' => $name]
            );
        }
    }

    private function seedConnectionTypes(): void
    {
        // [name, aliases]
        $connections = [
            ['Female Threaded', ['female threaded', 'fnpt', 'female npt']],
            ['Male Threaded', ['male threaded', 'mnpt', 'male npt']],
            ['NPT', ['npt', 'n.p.t.']],
            ['BSP', ['bsp']],
            ['BSPT', ['bspt']],
            ['Press-Fit', ['press-fit', 'press fit', 'press']],
            ['Solder', ['solder']],
            ['Sweat', ['sweat']],
            ['Grooved', ['grooved']],
            ['Flanged', ['flanged']],
            ['Push-Fit', ['push-fit', 'push fit']],
            ['PEX', ['pex']],
            ['Compression', ['compression']],
            ['Union', ['union']],
        ];

        foreach ($connections as [$name, $aliases]) {
            ConnectionType::updateOrCreate(
                ['normalized_name' => $this->norm($name)],
                ['name' => $name, 'aliases' => $aliases]
            );
        }
    }

    private function seedConnectionStandards(): void
    {
        $standards = [
            'ASME B1.20.1', 'BS EN 10226', 'NPT', 'BSP', 'BSPT',
            'ASME B16.5', 'EN 1092-1',
        ];

        foreach ($standards as $name) {
            ConnectionStandard::updateOrCreate(
                ['normalized_name' => $this->norm($name)],
                ['name' => $name]
            );
        }
    }

    private function seedPressureRatings(): void
    {
        // [name, numeric, unit, class, service]
        $ratings = [
            ['300 PSI', 300, 'psi', null, null],
            ['600 WOG', 600, 'psi', 'WOG', 'water-oil-gas'],
            ['PN16', 16, 'bar', 'PN', null],
            ['PN25', 25, 'bar', 'PN', null],
            ['Class 150', 150, 'class', 'Class', null],
            ['Class 300', 300, 'class', 'Class', null],
        ];

        foreach ($ratings as [$name, $num, $unit, $class, $service]) {
            PressureRating::updateOrCreate(
                ['normalized_value' => $this->norm($name)],
                [
                    'rating_name'    => $name,
                    'numeric_value'  => $num,
                    'unit'           => $unit,
                    'pressure_class' => $class,
                    'service_type'   => $service,
                ]
            );
        }
    }

    private function seedApprovals(): void
    {
        // [name, issuing_body, code, description] — code distinguishes UL 258 vs UL 842.
        $approvals = [
            ['UL Listed', 'UL', 'UL 258', 'Steel Underground Water Pipe / Sprinkler Trim'],
            ['UL Listed', 'UL', 'UL 842', 'Valves for Flammable Fluids'],
            ['FM Approved', 'FM', null, 'FM Global Approval'],
            ['LPCB', 'LPCB', null, 'Loss Prevention Certification Board'],
            ['WRAS', 'WRAS', null, 'Water Regulations Advisory Scheme'],
            ['NSF/ANSI 61', 'NSF', 'NSF/ANSI 61', 'Drinking Water System Components'],
            ['NSF/ANSI 372', 'NSF', 'NSF/ANSI 372', 'Lead Content'],
            ['SASO', 'SASO', null, 'Saudi Standards, Metrology and Quality Org'],
            ['Civil Defense Approved', 'Civil Defense', null, 'Saudi Civil Defense Approval'],
        ];

        foreach ($approvals as [$name, $body, $code, $desc]) {
            $key = $this->norm(trim($body . ' ' . ($code ?? $name)));
            Approval::updateOrCreate(
                ['normalized_key' => $key],
                [
                    'name'          => $name,
                    'issuing_body'  => $body,
                    'approval_code' => $code,
                    'description'   => $desc,
                ]
            );
        }
    }

    private function seedStandards(): void
    {
        // [code, name, organization]
        $standards = [
            ['MSS SP-110', 'Ball Valves Threaded, Socket-Welding, Solder Joint', 'MSS'],
            ['ASME B16.5', 'Pipe Flanges and Flanged Fittings', 'ASME'],
            ['EN 1092-1', 'Flanges and their joints', 'CEN'],
        ];

        foreach ($standards as [$code, $name, $org]) {
            Standard::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'organization' => $org]
            );
        }
    }
}
