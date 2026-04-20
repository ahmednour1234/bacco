<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BoqController as AdminBoqController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\QuotationController as AdminQuotationController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\SupplierController as AdminSupplierController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Enduser\BoqController as EnduserBoqController;
use App\Http\Controllers\Enduser\OrderController as EnduserOrderController;
use App\Http\Controllers\Enduser\AuthController as EnduserAuthController;
use App\Http\Controllers\Enduser\DashboardController as EnduserDashboardController;
use App\Http\Controllers\Enduser\ProfileController as EnduserProfileController;
use App\Http\Controllers\Enduser\ProjectController as EnduserProjectController;
use App\Http\Controllers\Enduser\QuotationController as EnduserQuotationController;
use App\Http\Controllers\Enduser\ReportController as EnduserReportController;
use App\Http\Controllers\Supplier\AuthController as SupplierAuthController;
use App\Http\Controllers\Supplier\DashboardController as SupplierDashboardController;
use App\Http\Controllers\Supplier\ProductController as SupplierProductController;
use App\Http\Controllers\Supplier\ProfileController as SupplierProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ─── Language Switch ──────────────────────────────────────────────────────────
Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'ar'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('locale.switch');

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

        // Notifications
        Route::get('/notifications', fn() => view('enduser.notifications'))->name('notifications');

        // Profile
        Route::get('/profile',          [EnduserProfileController::class, 'show'])->name('profile');
        Route::put('/profile',          [EnduserProfileController::class, 'update'])->name('profile.update');

        // Quotations
        Route::get('/quotations',              [EnduserQuotationController::class, 'index'])->name('quotations.index');
        Route::get('/quotations/create',        [EnduserQuotationController::class, 'create'])->name('quotations.create');
        Route::get('/quotations/{uuid}/edit',   [EnduserQuotationController::class, 'edit'])->name('quotations.edit');
        Route::get('/quotations/{uuid}/pdf',    [EnduserQuotationController::class, 'pdf'])->name('quotations.pdf');
        Route::get('/quotations/{uuid}',        [EnduserQuotationController::class, 'show'])->name('quotations.show');

        // Projects
        Route::get('/projects',        [EnduserProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/{uuid}', [EnduserProjectController::class, 'show'])->name('projects.show');

        // BOQs
        Route::get('/boqs',                     [EnduserBoqController::class, 'index'])->name('boqs.index');
        Route::get('/boqs/create',              [EnduserBoqController::class, 'create'])->name('boqs.create');
        Route::get('/boqs/create/{projectUuid}', [EnduserBoqController::class, 'create'])->name('boqs.create.project');
        Route::get('/boqs/{uuid}',              [EnduserBoqController::class, 'show'])->name('boqs.show');

        // Orders
        Route::get('/orders',        [EnduserOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{uuid}', [EnduserOrderController::class, 'show'])->name('orders.show');

        // Reports
        Route::get('/reports', [EnduserReportController::class, 'index'])->name('reports.index');
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

        // Notifications
        Route::get('/notifications', fn() => view('admin.notifications'))->name('notifications');

        // Profile
        Route::get('/profile',   [AdminProfileController::class, 'show'])->name('profile');
        Route::put('/profile',   [AdminProfileController::class, 'update'])->name('profile.update');

        // Quotations (read-only)
        Route::get('/quotations', [AdminQuotationController::class, 'index'])->name('quotations.index');
        Route::get('/quotations/{uuid}', [AdminQuotationController::class, 'show'])->name('quotations.show');

        // Catalog – Brands
        Route::resource('brands', AdminBrandController::class)->except(['show']);
        Route::post('/brands/import', [AdminBrandController::class, 'import'])->name('brands.import');
        Route::get('/brands/template', [AdminBrandController::class, 'template'])->name('brands.template');

        // Catalog – Categories
        Route::resource('categories', AdminCategoryController::class)->except(['show']);
        Route::post('/categories/import', [AdminCategoryController::class, 'import'])->name('categories.import');
        Route::get('/categories/template', [AdminCategoryController::class, 'template'])->name('categories.template');

        // Catalog – Products
        Route::resource('products', AdminProductController::class)->except(['show', 'store', 'update', 'destroy']);

        // Orders
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{uuid}', [AdminOrderController::class, 'show'])->name('orders.show');

        // BOQs
        Route::get('/boqs', [AdminBoqController::class, 'index'])->name('boqs.index');
        Route::get('/boqs/{uuid}', [AdminBoqController::class, 'show'])->name('boqs.show');

        // Suppliers
        Route::resource('suppliers', AdminSupplierController::class)->except(['destroy']);
        Route::post('/suppliers/{uuid}/toggle-status', [AdminSupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');

        // Supplier Products Approval
        Route::get('/supplier-products', fn() => view('admin.supplier-products'))->name('suppliers.products');

        // Users
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    });
});

// ─── Supplier Portal ──────────────────────────────────────────────────────────
Route::prefix('supplier')->name('supplier.')->group(function () {

    // Guest only
    Route::middleware('guest')->group(function () {
        Route::get('/register',            [SupplierAuthController::class, 'showRegister'])->name('register');
        Route::post('/register',           [SupplierAuthController::class, 'register'])->name('register.store');

        Route::get('/login',               [SupplierAuthController::class, 'showLogin'])->name('login');
        Route::post('/login',              [SupplierAuthController::class, 'login'])->name('login.store');

        Route::get('/forgot-password',       [SupplierAuthController::class, 'showForgotPassword'])->name('forgot-password');
        Route::post('/forgot-password/send', [SupplierAuthController::class, 'sendOtp'])->name('forgot-password.send');

        Route::get('/otp',                   [SupplierAuthController::class, 'showOtp'])->name('otp');
        Route::post('/otp/verify',           [SupplierAuthController::class, 'verifyOtp'])->name('otp.verify');

        Route::get('/reset-password',        [SupplierAuthController::class, 'showResetPassword'])->name('reset-password');
        Route::post('/reset-password',       [SupplierAuthController::class, 'updatePassword'])->name('reset-password.update');
    });

    // Authenticated suppliers only
    Route::middleware(['auth', 'supplier'])->group(function () {
        Route::get('/logout',    [SupplierAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [SupplierDashboardController::class, 'index'])->name('dashboard');

        // Notifications
        Route::get('/notifications', fn() => view('supplier.notifications'))->name('notifications');

        // Profile
        Route::get('/profile',   [SupplierProfileController::class, 'show'])->name('profile');
        Route::put('/profile',   [SupplierProfileController::class, 'update'])->name('profile.update');

        // Products catalogue
        Route::get('/products',                         [SupplierProductController::class, 'index'])->name('products.index');
        Route::get('/products/create',                  [SupplierProductController::class, 'create'])->name('products.create');
        Route::get('/products/{supplierProduct}/edit',  [SupplierProductController::class, 'edit'])->name('products.edit');
    });
});
