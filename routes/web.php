<?php

use App\Http\Controllers\Admin\VendorAdminController;
use App\Http\Controllers\Vendor\DashboardController;
use App\Http\Controllers\Vendor\OnboardingController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\QuotationController;
use App\Http\Controllers\Vendor\ImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;
        if (in_array($role, ['admin', 'super_admin'])) {
            return redirect()->route('admin.vendors.index');
        }
        return redirect()->route('vendor.dashboard');
    }
    return view('welcome');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Public Vendor Application (no auth required)
|--------------------------------------------------------------------------
*/
Route::get('/apply', [OnboardingController::class, 'index'])->name('vendor.apply');
Route::post('/apply', [OnboardingController::class, 'submit'])->name('vendor.apply.submit');
Route::get('/apply/success', fn() => view('vendor.apply-success'))->name('vendor.apply.success');

/*
|--------------------------------------------------------------------------
| Authenticated Vendor Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'vendor'])->prefix('vendor')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('vendor.dashboard');
    Route::get('/approval', [OnboardingController::class, 'approvalStatus'])->name('vendor.approval');

    // Products CRUD
    Route::get('/products', [ProductController::class, 'index'])->name('vendor.products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('vendor.products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('vendor.products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('vendor.products.edit');
    Route::post('/products/{product}', [ProductController::class, 'update'])->name('vendor.products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('vendor.products.destroy');

    // Quotations (vendor-created, standalone)
    Route::get('/quotations', [QuotationController::class, 'index'])->name('vendor.quotations.index');
    Route::get('/quotations/create', [QuotationController::class, 'create'])->name('vendor.quotations.create');
    Route::post('/quotations', [QuotationController::class, 'store'])->name('vendor.quotations.store');
    Route::get('/quotations/{quotation}/download', [QuotationController::class, 'download'])->name('vendor.quotations.download');
    Route::delete('/quotations/{quotation}', [QuotationController::class, 'destroy'])->name('vendor.quotations.destroy');

    // ─── Import Hub (website only) ──────────────────────────────────
    Route::prefix('import')->group(function () {
        Route::get('/',                    [ImportController::class, 'index'])    ->name('vendor.import.index');
        Route::post('/website',            [ImportController::class, 'website'])  ->name('vendor.import.website');
        Route::get('/jobs/{job}',          [ImportController::class, 'jobStatus'])->name('vendor.import.status');
        Route::get('/jobs/{job}/poll',     [ImportController::class, 'poll'])     ->name('vendor.import.poll');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/vendors', [VendorAdminController::class, 'index'])->name('admin.vendors.index');
    Route::get('/vendors/{id}', [VendorAdminController::class, 'show'])->name('admin.vendors.show');
    Route::post('/vendors/{id}/approve', [VendorAdminController::class, 'approve'])->name('admin.vendors.approve');
    Route::post('/vendors/{id}/reject', [VendorAdminController::class, 'reject'])->name('admin.vendors.reject');
});
