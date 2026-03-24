<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{

    public function run(): void
    {
        $roles = [
            [
                'name'         => 'admin',
                'display_name' => 'Administrator',
                'description'  => 'Full platform access.',
            ],
            [
                'name'         => 'employee',
                'display_name' => 'Employee',
                'description'  => 'Internal team member.',
            ],
            [
                'name'         => 'client',
                'display_name' => 'Client',
                'description'  => 'Customer placing quotation requests and orders.',
            ],
            [
                'name'         => 'supplier',
                'display_name' => 'Supplier',
                'description'  => 'External supplier providing product pricing.',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
