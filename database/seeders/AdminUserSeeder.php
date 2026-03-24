<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{

    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@qimta.sa'],
            [
                'name'              => 'Qimta Admin',
                'phone'             => '+966500000000',
                'email_verified_at' => now(),
                'password'          => 'Admin@12345',   // hashed by the 'hashed' cast
                'user_type'         => UserTypeEnum::Admin->value,
                'active'            => true,
            ]
        );

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && ! $admin->roles()->where('role_id', $adminRole->id)->exists()) {
            $admin->roles()->attach($adminRole);
        }
    }
}
