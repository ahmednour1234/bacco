<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
use App\Models\EmployeeProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employeeRole = Role::where('name', 'employee')->first();

        $employees = [
            [
                'user' => [
                    'name'              => 'Sara Al-Harbi',
                    'email'             => 'sara@qimta.sa',
                    'phone'             => '+966511000001',
                    'password'          => 'Employee@123',
                    'user_type'         => UserTypeEnum::Employee->value,
                    'email_verified_at' => now(),
                    'active'            => true,
                ],
                'profile' => [
                    'department' => 'Sales',
                    'position'   => 'Sales Manager',
                    'national_id' => '1090000001',
                    'hire_date'  => '2022-03-15',
                ],
            ],
            [
                'user' => [
                    'name'              => 'Khalid Al-Otaibi',
                    'email'             => 'khalid@qimta.sa',
                    'phone'             => '+966511000002',
                    'password'          => 'Employee@123',
                    'user_type'         => UserTypeEnum::Employee->value,
                    'email_verified_at' => now(),
                    'active'            => true,
                ],
                'profile' => [
                    'department' => 'Engineering',
                    'position'   => 'Site Engineer',
                    'national_id' => '1090000002',
                    'hire_date'  => '2021-07-01',
                ],
            ],
            [
                'user' => [
                    'name'              => 'Nora Al-Zahrani',
                    'email'             => 'nora@qimta.sa',
                    'phone'             => '+966511000003',
                    'password'          => 'Employee@123',
                    'user_type'         => UserTypeEnum::Employee->value,
                    'email_verified_at' => now(),
                    'active'            => true,
                ],
                'profile' => [
                    'department' => 'Procurement',
                    'position'   => 'Procurement Officer',
                    'national_id' => '1090000003',
                    'hire_date'  => '2023-01-10',
                ],
            ],
            [
                'user' => [
                    'name'              => 'Faisal Al-Ghamdi',
                    'email'             => 'faisal@qimta.sa',
                    'phone'             => '+966511000004',
                    'password'          => 'Employee@123',
                    'user_type'         => UserTypeEnum::Employee->value,
                    'email_verified_at' => now(),
                    'active'            => true,
                ],
                'profile' => [
                    'department' => 'Logistics',
                    'position'   => 'Logistics Coordinator',
                    'national_id' => '1090000004',
                    'hire_date'  => '2022-11-20',
                ],
            ],
        ];

        foreach ($employees as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['user']['email']],
                $data['user']
            );

            EmployeeProfile::firstOrCreate(
                ['user_id' => $user->id],
                $data['profile']
            );

            if ($employeeRole && ! $user->roles()->where('role_id', $employeeRole->id)->exists()) {
                $user->roles()->attach($employeeRole);
            }
        }
    }
}
