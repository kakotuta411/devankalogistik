<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PrintSettingController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth', 'readonly'])->group(function () {
    Route::get('/', fn() => redirect('/dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('shipments', ShipmentController::class);
    Route::get('/shipments/export/csv', [ShipmentController::class, 'exportCsv'])->name('shipments.export.csv');
    Route::get('/shipments/export/pdf', [ShipmentController::class, 'exportPdf'])->name('shipments.export.pdf');
    Route::post('/shipments/{id}/hold', [ShipmentController::class, 'hold'])->name('shipments.hold');
    Route::post('/shipments/{id}/cancel', [ShipmentController::class, 'cancel'])->name('shipments.cancel');

    Route::resource('manifests', ManifestController::class);
    Route::get('/manifests/export/csv', [ManifestController::class, 'exportCsv'])->name('manifests.export.csv');
    Route::get('/manifests/export/pdf', [ManifestController::class, 'exportPdf'])->name('manifests.export.pdf');
    Route::post('/manifests/{id}/dispatch', [ManifestController::class, 'dispatch'])->name('manifests.dispatch');
    Route::post('/manifests/{id}/receive', [ManifestController::class, 'receive'])->name('manifests.receive');
    Route::post('/manifests/{id}/complete', [ManifestController::class, 'complete'])->name('manifests.complete');

    Route::middleware('role:Admin,Head_of_Branch')->group(function () {
        Route::resource('branches', BranchController::class);
    });

    Route::middleware('role:Admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('/admin/template-management', [PrintSettingController::class, 'index'])->name('template-management');
        Route::post('/admin/template-management', [PrintSettingController::class, 'store'])->name('template-management.store');
    Route::post('/admin/template-management/upload', [PrintSettingController::class, 'uploadTemplate'])->name('template-management.upload');
    });

    Route::resource('invoices', InvoiceController::class);
    Route::get('/invoices/export/csv', [InvoiceController::class, 'exportCsv'])->name('invoices.export.csv');
    Route::get('/invoices/export/pdf', [InvoiceController::class, 'exportPdf'])->name('invoices.export.pdf');
    Route::post('/invoices/{id}/mark-paid', [InvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
    Route::post('/invoices/{id}/mark-cancelled', [InvoiceController::class, 'markCancelled'])->name('invoices.mark-cancelled');
    Route::post('/invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('invoices.payment');
    Route::post('/invoices/{id}/add-item', [InvoiceController::class, 'addItem'])->name('invoices.add-item');

    // Print routes
    Route::get('/print/invoices/{id}/pdf', [PrintController::class, 'invoice'])->name('print.invoice');
    Route::get('/print/shipments/{id}/label', [PrintController::class, 'shipmentLabel'])->name('print.shipment-label');
    Route::get('/print/shipments/{id}/resi', [PrintController::class, 'shipmentResi'])->name('print.shipment-resi');
    Route::get('/print/manifests/{id}/sheet', [PrintController::class, 'manifestSheet'])->name('print.manifest-sheet');
    Route::get('/print/shipments/list', [PrintController::class, 'shipmentList'])->name('print.shipment-list');

    // Purchase Orders
    Route::resource('purchase-orders', PurchaseOrderController::class);

    // Customer API
    Route::get('/api/customers/search', [\App\Http\Controllers\CustomerController::class, 'search'])->name('api.customers.search');
    Route::post('/api/customers', [\App\Http\Controllers\CustomerController::class, 'store'])->name('api.customers.store');
    Route::get('/api/customers/{customer}/shipments', [\App\Http\Controllers\CustomerController::class, 'shipmentsByCustomer'])->name('api.customers.shipments');
    Route::get('/api/check-po', [\App\Http\Controllers\CustomerController::class, 'checkPoNumber'])->name('api.check-po');

    // Region API for cascading selects
    Route::get('/api/regions/provinces', [RegionController::class, 'provinces']);
    Route::get('/api/regions/regencies', [RegionController::class, 'regencies']);
    Route::get('/api/regions/districts', [RegionController::class, 'districts']);
    Route::get('/api/regions/villages', [RegionController::class, 'villages']);
});
