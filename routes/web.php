<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Enduser\AuthController as EnduserAuthController;
use App\Http\Controllers\Enduser\DashboardController as EnduserDashboardController;
use App\Http\Controllers\Enduser\ProfileController as EnduserProfileController;
use App\Http\Controllers\Enduser\QuotationController as EnduserQuotationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ─── End-user (Client) Auth ──────────────────────────────────────────────────
Route::prefix('enduser')->name('enduser.')->group(function () {

    // Guest only
    Route::middleware('guest')->group(function () {
        Route::get('/register',  [EnduserAuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [EnduserAuthController::class, 'store'])->name('register.store');

        Route::get('/login',     [EnduserAuthController::class, 'showLogin'])->name('login');
        Route::post('/login',    [EnduserAuthController::class, 'login'])->name('login.store');

        // ── Forgot password → OTP → reset (3-step flow) ──────────────────
        Route::get('/forgot-password',       [EnduserAuthController::class, 'showForgotPassword'])->name('forgot-password');
        Route::post('/forgot-password/send', [EnduserAuthController::class, 'sendOtp'])->name('forgot-password.send');

        Route::get('/otp',                   [EnduserAuthController::class, 'showOtp'])->name('otp');
        Route::post('/otp/verify',           [EnduserAuthController::class, 'verifyOtp'])->name('otp.verify');

        Route::get('/reset-password',        [EnduserAuthController::class, 'showResetPassword'])->name('reset-password');
        Route::post('/reset-password',       [EnduserAuthController::class, 'updatePassword'])->name('reset-password.update');
    });

    // Authenticated only
    Route::middleware('auth')->group(function () {
        Route::get('/logout',           [EnduserAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard',        [EnduserDashboardController::class, 'index'])->name('dashboard');

        // Profile
        Route::get('/profile',          [EnduserProfileController::class, 'show'])->name('profile');
        Route::put('/profile',          [EnduserProfileController::class, 'update'])->name('profile.update');

        // Quotations
        Route::get('/quotations',        [EnduserQuotationController::class, 'index'])->name('quotations.index');
        Route::get('/quotations/create', [EnduserQuotationController::class, 'create'])->name('quotations.create');
        Route::get('/quotations/{uuid}', [EnduserQuotationController::class, 'show'])->name('quotations.show');
    });
});

// ─── Admin / Employee Portal ──────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest only
    Route::middleware('guest')->group(function () {
        Route::get('/login',  [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.store');
    });

    // Authenticated employees / admins only
    Route::middleware(['auth', 'employee'])->group(function () {
        Route::get('/logout',    [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Profile
        Route::get('/profile',   [AdminProfileController::class, 'show'])->name('profile');
        Route::put('/profile',   [AdminProfileController::class, 'update'])->name('profile.update');

        // Catalog – Brands
        Route::resource('brands', AdminBrandController::class)->except(['show']);

        // Catalog – Categories
        Route::resource('categories', AdminCategoryController::class)->except(['show']);

        // Catalog – Products
        Route::resource('products', AdminProductController::class)->except(['show', 'store', 'update', 'destroy']);
    });
});
