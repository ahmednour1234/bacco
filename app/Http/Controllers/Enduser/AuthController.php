<?php

namespace App\Http\Controllers\Enduser;

use App\Http\Controllers\Controller;
use App\Http\Requests\Enduser\RegisterRequest;
use App\Models\Boq;
use App\Models\User;
use App\Services\Enduser\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        return view('enduser.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = $this->authService->register($request);

        $boq = $this->claimGuestBoq($request, $user);

        if ($boq) {
            return redirect()
                ->route('enduser.quotations.show', ['uuid' => $boq->quotationRequests()->latest()->first()?->uuid ?? $boq->uuid])
                ->with('success', 'مرحباً! تم ربط جدول الكميات بحسابك.');
        }

        return redirect()
            ->route('enduser.dashboard')
            ->with('success', 'Welcome! Your account has been created.');
    }

    // =========================================================================
    // Login
    // =========================================================================

    public function showLogin(): View
    {
        return view('enduser.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $loggedIn = $this->authService->login(
            email:    $credentials['email'],
            password: $credentials['password'],
            remember: $request->boolean('remember'),
        );

        if (! $loggedIn) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        $request->session()->regenerate();

        $boq = $this->claimGuestBoq($request, auth()->user());

        if ($boq) {
            $quotation = $boq->quotationRequests()->latest()->first();
            return redirect()->route('enduser.quotations.show', ['uuid' => $quotation?->uuid ?? $boq->uuid])
                ->with('success', 'مرحباً! تم ربط جدول الكميات بحسابك.');
        }

        return redirect()->intended(route('enduser.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->authService->logout();

        return redirect()->route('enduser.login');
    }

    // =========================================================================
    // Guest BOQ Claim
    // =========================================================================

    /**
     * If the session contains a pending guest BOQ, transfer ownership to the
     * newly authenticated user. Returns the claimed Boq or null.
     */
    private function claimGuestBoq(Request $request, ?User $user): ?Boq
    {
        if (! $user) {
            return null;
        }

        $uuid  = $request->session()->get('pending_guest_boq_uuid');
        $token = $request->session()->get('pending_guest_boq_token');

        if (! $uuid || ! $token) {
            return null;
        }

        $boq = Boq::where('uuid', $uuid)
            ->where('guest_token', $token)
            ->whereNull('client_id')
            ->first();

        if (! $boq) {
            return null;
        }

        // Claim the BOQ
        $boq->update(['client_id' => $user->id, 'guest_token' => null]);

        // Claim the linked guest project
        if ($boq->project_id) {
            $boq->project()
                ->where('is_guest', true)
                ->whereNull('client_id')
                ->update(['client_id' => $user->id, 'is_guest' => false]);
        }

        // Claim any quotation requests linked to this BOQ
        $boq->quotationRequests()
            ->whereNull('client_id')
            ->update(['client_id' => $user->id]);

        $request->session()->forget(['pending_guest_boq_uuid', 'pending_guest_boq_token']);

        return $boq->fresh();
    }

    // =========================================================================
    // Forgot Password – step 1 : enter email
    // =========================================================================

    public function showForgotPassword(): View
    {
        return view('enduser.forgot-password');
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
                ->withErrors(['email' => 'No account found with this email address.']);
        }

        // Store the email in session so the OTP page knows who to verify
        session(['otp_email' => $request->email]);

        return redirect()
            ->route('enduser.otp')
            ->with('success', 'A 4-digit code has been sent to your email.');
    }

    // =========================================================================
    // Forgot Password – step 2 : enter OTP
    // =========================================================================

    public function showOtp(): View
    {
        // Guard: must have come from the forgot-password flow
        abort_unless(session()->has('otp_email'), 403);

        return view('enduser.otp');
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:4'],
        ]);

        $email = session('otp_email');

        if (! $email) {
            return redirect()->route('enduser.forgot-password');
        }

        $valid = $this->authService->verifyOtp($email, $request->otp);

        if (! $valid) {
            return back()->withErrors(['otp' => 'Invalid or expired code. Please try again.']);
        }

        // Mark OTP as verified; keep email for the reset step
        session(['otp_verified' => true]);

        return redirect()->route('enduser.reset-password');
    }

    // =========================================================================
    // Forgot Password – step 3 : set new password
    // =========================================================================

    public function showResetPassword(): View
    {
        // Guard: must have verified OTP first
        abort_unless(session('otp_verified') && session()->has('otp_email'), 403);

        return view('enduser.reset-password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = session('otp_email');

        if (! $email || ! session('otp_verified')) {
            return redirect()->route('enduser.forgot-password');
        }

        $this->authService->resetPassword($email, $request->password);

        // Clean up the session
        session()->forget(['otp_email', 'otp_verified']);

        return redirect()
            ->route('enduser.login')
            ->with('success', 'Password updated successfully. Please sign in.');
    }
}
