<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Services\Supplier\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    // =========================================================================
    // Register
    // =========================================================================

    public function showRegister(): View
    {
        return view('supplier.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'        => ['required', 'string', 'max:50'],
            'password'     => ['required', 'confirmed', Password::min(8)],
            'company_name' => ['nullable', 'string', 'max:255'],
            'division'     => ['nullable', 'string', 'max:255'],
            'address'      => ['nullable', 'string', 'max:500'],
            'city'         => ['nullable', 'string', 'max:100'],
            'country'      => ['nullable', 'string', 'max:100'],
        ]);

        $this->authService->register($request->only([
            'name', 'email', 'phone', 'password',
            'company_name', 'division', 'address', 'city', 'country',
        ]));

        return redirect()
            ->route('supplier.login')
            ->with('success', 'Registration submitted! Your account is pending admin approval. You will be able to sign in once activated.');
    }

    // =========================================================================
    // Login
    // =========================================================================

    public function showLogin(): View
    {
        return view('supplier.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $result = $this->authService->login(
            email:    $request->string('email')->toString(),
            password: $request->string('password')->toString(),
            remember: $request->boolean('remember'),
        );

        if ($result === null) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Your account has been deactivated. Please contact support.']);
        }

        if ($result === false) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('supplier.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout();

        return redirect()->route('supplier.login');
    }

    // =========================================================================
    // Forgot Password – step 1 : enter email
    // =========================================================================

    public function showForgotPassword(): View
    {
        return view('supplier.forgot-password');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $sent = $this->authService->sendOtp($request->email);

        if (! $sent) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'No supplier account found with this email address.']);
        }

        session(['supplier_otp_email' => $request->email]);

        return redirect()
            ->route('supplier.otp')
            ->with('success', 'A 4-digit code has been sent to your email.');
    }

    // =========================================================================
    // Forgot Password – step 2 : enter OTP
    // =========================================================================

    public function showOtp(): View
    {
        abort_unless(session()->has('supplier_otp_email'), 403);

        return view('supplier.otp');
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:4'],
        ]);

        $email = session('supplier_otp_email');

        if (! $email) {
            return redirect()->route('supplier.forgot-password');
        }

        $valid = $this->authService->verifyOtp($email, $request->otp);

        if (! $valid) {
            return back()->withErrors(['otp' => 'Invalid or expired code. Please try again.']);
        }

        session(['supplier_otp_verified' => true]);

        return redirect()->route('supplier.reset-password');
    }

    // =========================================================================
    // Forgot Password – step 3 : set new password
    // =========================================================================

    public function showResetPassword(): View
    {
        abort_unless(session('supplier_otp_verified') && session()->has('supplier_otp_email'), 403);

        return view('supplier.reset-password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = session('supplier_otp_email');

        if (! $email || ! session('supplier_otp_verified')) {
            return redirect()->route('supplier.forgot-password');
        }

        $this->authService->resetPassword($email, $request->password);

        session()->forget(['supplier_otp_email', 'supplier_otp_verified']);

        return redirect()
            ->route('supplier.login')
            ->with('success', 'Password updated successfully. Please sign in.');
    }
}
