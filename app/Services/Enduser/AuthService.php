<?php

namespace App\Services\Enduser;

use App\Http\Requests\Enduser\RegisterRequest;
use App\Models\User;
use App\Repositories\Enduser\AuthRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    // OTP lives for 10 minutes
    private const OTP_TTL_MINUTES = 10;

    public function __construct(
        private readonly AuthRepository $authRepository
    ) {}

    // -------------------------------------------------------------------------
    // Register
    // -------------------------------------------------------------------------

    public function register(RegisterRequest $request): User
    {
        $user = $this->authRepository->createClient([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
            'phone'    => $request->phone,
            'role'     => $request->role,
            'company'  => $request->company,
        ]);

        Auth::login($user);

        return $user;
    }

    // -------------------------------------------------------------------------
    // Login
    // -------------------------------------------------------------------------

    public function login(string $email, string $password, bool $remember = false): bool
    {
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

    // -------------------------------------------------------------------------
    // Forgot Password – OTP flow
    // -------------------------------------------------------------------------

    /**
     * Generate a 4-digit OTP, store it in cache, and (log / send) it.
     * Returns false when the email is not registered.
     */
    public function sendOtp(string $email): bool
    {
        if (! $this->authRepository->findByEmail($email)) {
            return false;
        }

        $otp = app()->environment(['local', 'testing']) ? '1111' : (string) random_int(1000, 9999);

        Cache::put(
            key:   $this->otpCacheKey($email),
            value: $otp,
            ttl:   now()->addMinutes(self::OTP_TTL_MINUTES)
        );

        // ── Send via mail (swap this for your Mailable when mail is configured) ──
        // Mail::to($email)->send(new \App\Mail\OtpMail($otp));

        // Visible in storage/logs/laravel.log during development
        Log::info("[Qimta OTP] email={$email} otp={$otp}");

        return true;
    }

    /**
     * Check whether the supplied OTP matches what is stored in cache.
     */
    public function verifyOtp(string $email, string $otp): bool
    {
        $stored = Cache::get($this->otpCacheKey($email));

        if (! $stored || $stored !== $otp) {
            return false;
        }

        // Invalidate the OTP after successful verification
        Cache::forget($this->otpCacheKey($email));

        return true;
    }

    /**
     * Update the password for the given email address.
     */
    public function resetPassword(string $email, string $newPassword): bool
    {
        return $this->authRepository->updatePassword($email, $newPassword);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function otpCacheKey(string $email): string
    {
        return 'enduser_otp_' . md5(strtolower(trim($email)));
    }
}
