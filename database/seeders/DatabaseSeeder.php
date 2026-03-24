<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the Qimta platform database.
     *
     * Execution order matters — roles must exist before the admin user seeder
     * tries to attach a role.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UnitSeeder::class,
            CategoryBrandSeeder::class,
            AdminUserSeeder::class,
            EmployeeSeeder::class,
        ]);
    }
}
