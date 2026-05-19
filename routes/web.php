<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BoqController as AdminBoqController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\ContactSubmissionController as AdminContactSubmissionController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\QuotationController as AdminQuotationController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\SupplierController as AdminSupplierController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\AdminUserController as AdminAdminUserController;
use App\Http\Controllers\Admin\Catalog\CatalogImportController;
use App\Http\Controllers\Admin\Catalog\CatalogProductController as CatalogProductListController;
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
use Illuminate\Support\Facades\Storage;

// ─── Serve public storage files directly (no symlink required) ────────────────
Route::get('/public/storage/{path}', function (string $path) {
    $disk     = Storage::disk('public');
    $safePath = ltrim($path, '/');

    if (! $disk->exists($safePath)) {
        abort(404);
    }

    return $disk->response($safePath);
})->where('path', '.*')->name('storage.serve');

Route::get('/', [\App\Http\Controllers\CatalogController::class, 'home']);

// ─── Sitemaps ─────────────────────────────────────────────────────────────────
Route::get('/sitemap.xml',         [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemap-en.xml',      [\App\Http\Controllers\SitemapController::class, 'en'])->name('sitemap.en');
Route::get('/sitemap-ar.xml',      [\App\Http\Controllers\SitemapController::class, 'ar'])->name('sitemap.ar');
Route::get('/sitemap-news.xml',    [\App\Http\Controllers\SitemapController::class, 'news'])->name('sitemap.news');
Route::get('/sitemap-catalog.xml', [\App\Http\Controllers\SitemapController::class, 'catalog'])->name('sitemap.catalog');

// ─── Generic /dashboard redirect → enduser dashboard ─────────────────────────
Route::get('/dashboard', function () {
    return redirect()->route('enduser.dashboard');
});

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/for-brands', function () {
    return view('for-brands');
})->name('for-brands');

// ─── Public Catalog ───────────────────────────────────────────────────────────
Route::get('/catalog', [\App\Http\Controllers\CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalog/category/{slug}', [\App\Http\Controllers\CatalogController::class, 'showCategory'])->name('catalog.category');
Route::get('/catalog/{divisionSlug}/{itemSlug}', [\App\Http\Controllers\CatalogController::class, 'showItem'])->name('catalog.item');
Route::get('/catalog/{slug}', [\App\Http\Controllers\CatalogController::class, 'show'])->name('catalog.division');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');
Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'store'])->name('contact.store');

Route::get('/privacy-policy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/security', function () {
    return view('security');
})->name('security');

Route::get('/support', function () {
    return view('support');
})->name('support');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

Route::get('/cookie-policy', function () {
    return view('cookie');
})->name('cookie');

// ─── SEO Landing Pages ────────────────────────────────────────────────────────
Route::get('/construction-pricing', fn () => view('landing.construction-pricing'))->name('landing.construction-pricing');
Route::get('/boq-pricing',          fn () => view('landing.boq-pricing'))->name('landing.boq-pricing');
Route::get('/procurement-platform', fn () => view('landing.procurement-platform'))->name('landing.procurement-platform');

Route::get('/news', [App\Http\Controllers\NewsController::class, 'index'])->name('news');
Route::get('/news/{slugOrUuid}', [App\Http\Controllers\NewsController::class, 'show'])->name('news.show');

// ─── Arabic URL prefix — crawlable by Google ──────────────────────────────────
// These mirror every public EN route under /ar/ so Googlebot can index Arabic.
// SetLocale middleware detects the /ar/ prefix and sets locale to 'ar'.
Route::prefix('ar')->name('ar.')->group(function () {
    Route::get('/',           [\App\Http\Controllers\CatalogController::class, 'home'])->name('home');
    Route::get('/about',      fn () => view('about'))->name('about');
    Route::get('/for-brands', fn () => view('for-brands'))->name('for-brands');
    Route::get('/contact',    fn () => view('contact'))->name('contact');
    Route::post('/contact',   [\App\Http\Controllers\ContactController::class, 'store'])->name('contact.store');
    Route::get('/support',    fn () => view('support'))->name('support');
    Route::get('/privacy-policy', fn () => view('privacy'))->name('privacy');
    Route::get('/security',   fn () => view('security'))->name('security');
    Route::get('/terms',      fn () => view('terms'))->name('terms');
    Route::get('/cookie-policy', fn () => view('cookie'))->name('cookie');
    // ─── AR SEO Landing Pages ─────────────────────────────────────────────────
    Route::get('/construction-pricing', fn () => view('landing.construction-pricing'))->name('landing.construction-pricing');
    Route::get('/boq-pricing',          fn () => view('landing.boq-pricing'))->name('landing.boq-pricing');
    Route::get('/procurement-platform', fn () => view('landing.procurement-platform'))->name('landing.procurement-platform');
    Route::get('/news',       [App\Http\Controllers\NewsController::class, 'index'])->name('news');
    Route::get('/news/{slugOrUuid}', [App\Http\Controllers\NewsController::class, 'show'])->name('news.show');
    Route::get('/catalog',    [\App\Http\Controllers\CatalogController::class, 'index'])->name('catalog.index');
    Route::get('/catalog/category/{slug}', [\App\Http\Controllers\CatalogController::class, 'showCategory'])->name('catalog.category');
    Route::get('/catalog/{divisionSlug}/{itemSlug}', [\App\Http\Controllers\CatalogController::class, 'showItem'])->name('catalog.item');
    Route::get('/catalog/{slug}', [\App\Http\Controllers\CatalogController::class, 'show'])->name('catalog.division');
});

// ─── Language Switch ──────────────────────────────────────────────────────────
Route::get('/locale/{locale}', function (string $locale) {
    if (!in_array($locale, ['en', 'ar'])) {
        return redirect()->back();
    }
    session(['locale' => $locale]);

    $current = request()->header('Referer', '/');
    $path    = parse_url($current, PHP_URL_PATH) ?? '/';

    // Portal paths don't use /ar/ URL prefix — just save session and stay
    $isPortal = str_starts_with($path, '/admin')
             || str_starts_with($path, '/enduser')
             || str_starts_with($path, '/supplier');

    if ($isPortal) {
        return redirect()->back();
    }

    if ($locale === 'ar') {
        // If not already under /ar/, prepend it
        if (!str_starts_with($path, '/ar/') && $path !== '/ar') {
            $arPath = '/ar' . ($path === '/' ? '/' : $path);
            return redirect($arPath);
        }
    } else {
        // Strip /ar prefix to go back to EN URL
        if (str_starts_with($path, '/ar/')) {
            return redirect(substr($path, 3) ?: '/');
        }
        if ($path === '/ar') {
            return redirect('/');
        }
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
        Route::get('/boqs',                      [EnduserBoqController::class, 'index'])->name('boqs.index');
        Route::get('/boqs/data',                 [EnduserBoqController::class, 'data'])->name('boqs.data');
        Route::get('/boqs/create',               [EnduserBoqController::class, 'create'])->name('boqs.create');
        Route::get('/boqs/create/{projectUuid}',  [EnduserBoqController::class, 'create'])->name('boqs.create.project');
        Route::get('/boqs/draft-status',         [EnduserBoqController::class, 'draftStatus'])->name('boqs.draft-status');
        Route::post('/boqs/{uuid}/convert',      [EnduserBoqController::class, 'convert'])->name('boqs.convert');
        Route::post('/boqs/{id}/duplicate',      [EnduserBoqController::class, 'duplicate'])->name('boqs.duplicate');
        Route::delete('/boqs/{id}',              [EnduserBoqController::class, 'destroy'])->name('boqs.destroy');
        Route::get('/boqs/{uuid}',               [EnduserBoqController::class, 'show'])->name('boqs.show');

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
        Route::get('/quotations/{uuid}/pdf', [AdminQuotationController::class, 'pdf'])->name('quotations.pdf');
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
        Route::get('/orders/export', [AdminOrderController::class, 'export'])->name('orders.export');        Route::get('/orders/{uuid}/pdf', [AdminOrderController::class, 'pdf'])->name('orders.pdf');        Route::get('/orders/{uuid}', [AdminOrderController::class, 'show'])->name('orders.show');

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

        // Admins management (admin-only)
        Route::get('/admins', [AdminAdminUserController::class, 'index'])->name('admins.index');
        Route::get('/admins/create', [AdminAdminUserController::class, 'create'])->name('admins.create');
        Route::post('/admins', [AdminAdminUserController::class, 'store'])->name('admins.store');
        Route::get('/admins/{admin}/edit', [AdminAdminUserController::class, 'edit'])->name('admins.edit');
        Route::put('/admins/{admin}', [AdminAdminUserController::class, 'update'])->name('admins.update');

        // Articles
        Route::resource('articles', AdminArticleController::class)->except(['show', 'store', 'update']);
        Route::post('/articles/upload-media', [AdminArticleController::class, 'uploadMedia'])->name('articles.upload-media');

        // Contact Submissions
        Route::get('/contact-submissions', [AdminContactSubmissionController::class, 'index'])->name('contact-submissions.index');
        Route::get('/contact-submissions/{contactSubmission}', [AdminContactSubmissionController::class, 'show'])->name('contact-submissions.show');
        Route::patch('/contact-submissions/{contactSubmission}/status', [AdminContactSubmissionController::class, 'updateStatus'])->name('contact-submissions.update-status');
        Route::delete('/contact-submissions/{contactSubmission}', [AdminContactSubmissionController::class, 'destroy'])->name('contact-submissions.destroy');

        // ── Product Catalog (separate MySQL DB) ──────────────────────────────
        Route::prefix('catalog')->name('catalog.')->group(function () {
            // Imports
            Route::get('imports',               [CatalogImportController::class, 'index'])->name('imports.index');
            Route::get('imports/create',        [CatalogImportController::class, 'create'])->name('imports.create');
            Route::post('imports',              [CatalogImportController::class, 'store'])->name('imports.store');
            Route::get('imports/{id}',          [CatalogImportController::class, 'show'])->name('imports.show');
            Route::get('imports/{id}/failed',   [CatalogImportController::class, 'failedRows'])->name('imports.failed-rows');
            Route::get('imports/{id}/progress', [CatalogImportController::class, 'progress'])->name('imports.progress');
            Route::post('queue/run',              [CatalogImportController::class, 'runQueue'])->name('queue.run');

            // Products
            Route::get('products', [CatalogProductListController::class, 'index'])->name('products.index');
        });
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
