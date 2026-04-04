<?php

namespace App\Services\Supplier;

use App\Enums\UserTypeEnum;
use App\Models\SupplierProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthService
{
    private const OTP_TTL_MINUTES = 10;

    public function login(string $email, string $password, bool $remember = false): bool|null
    {
        $user = User::where('email', $email)->first();

        if (! $user || $user->user_type !== UserTypeEnum::Supplier) {
            return false;
        }

        if (! $user->active) {
            return null; // deactivated account
        }

        return Auth::attempt(
            ['email' => $email, 'password' => $password],
            $remember
        );
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    public function sendOtp(string $email): bool
    {
        $user = User::where('email', $email)
            ->where('user_type', UserTypeEnum::Supplier)
            ->first();

        if (! $user) {
            return false;
        }

        $otp = app()->environment(['local', 'testing']) ? '1111' : (string) random_int(1000, 9999);

        Cache::put(
            key:   $this->otpCacheKey($email),
            value: $otp,
            ttl:   now()->addMinutes(self::OTP_TTL_MINUTES)
        );

        Log::info("[Qimta Supplier OTP] email={$email} otp={$otp}");

        return true;
    }

    public function verifyOtp(string $email, string $otp): bool
    {
        $stored = Cache::get($this->otpCacheKey($email));

        if (! $stored || $stored !== $otp) {
            return false;
        }

        Cache::forget($this->otpCacheKey($email));

        return true;
    }

    public function resetPassword(string $email, string $newPassword): bool
    {
        return (bool) User::where('email', $email)
            ->where('user_type', UserTypeEnum::Supplier)
            ->update(['password' => bcrypt($newPassword)]);
    }

    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'      => $data['name'],
                'email'     => $data['email'],
                'phone'     => $data['phone'] ?? null,
                'password'  => bcrypt($data['password']),
                'user_type' => UserTypeEnum::Supplier,
                'active'    => false, // requires admin activation
            ]);

            SupplierProfile::create([
                'user_id'      => $user->id,
                'company_name' => $data['company_name'] ?? null,
                'division'     => $data['division'] ?? null,
                'address'      => $data['address'] ?? null,
                'city'         => $data['city'] ?? null,
                'country'      => $data['country'] ?? null,
            ]);

            return $user;
        });
    }

    private function otpCacheKey(string $email): string
    {
        return 'supplier_otp_' . md5(strtolower(trim($email)));
    }
}
