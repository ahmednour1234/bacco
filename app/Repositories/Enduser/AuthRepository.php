<?php

namespace App\Repositories\Enduser;

use App\Enums\UserTypeEnum;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthRepository
{
    /**
     * Create a new client user together with their profile.
     *
     * @param  array{name:string,email:string,password:string,phone:string,role:string,company:string}  $data
     */
    public function createClient(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            /** @var User $user */
            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'password'  => $data['password'],
                'user_type' => UserTypeEnum::Client,
                'active'    => true,
            ]);

            ClientProfile::create([
                'user_id'      => $user->id,
                'phone'        => $data['phone'],
                'role'         => $data['role'],
                'company_name' => $data['company'],
            ]);

            return $user;
        });
    }

    /**
     * Find a user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Update the password of a user identified by email.
     */
    public function updatePassword(string $email, string $newPassword): bool
    {
        return (bool) User::where('email', $email)
            ->update(['password' => bcrypt($newPassword)]);
    }
}
