<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Repositories\Admin\AuthRepository;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function __construct(
        private readonly AuthRepository $authRepository
    ) {}

    // -------------------------------------------------------------------------
    // Login
    // -------------------------------------------------------------------------

    /**
     * Attempt to authenticate with the given credentials.
     *
     * Returns true on success.
     * Returns false when credentials are wrong.
     * Returns null when credentials are correct but the user is not an admin
     * or employee (caller should treat this as an authorization failure).
     */
    public function login(string $email, string $password, bool $remember = false): bool|null
    {
        if (! Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            return false;
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $this->authRepository->isAuthorized($user)) {
            Auth::logout();
            return null;
        }

        return true;
    }

    // -------------------------------------------------------------------------
    // Logout
    // -------------------------------------------------------------------------

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }
}
