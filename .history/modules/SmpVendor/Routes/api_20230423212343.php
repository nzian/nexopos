<?php

use Modules\SmpVendor\Http\Controllers\VendorsController;
use Modules\SmpVendor\Http\Controllers\VendorReportsController;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

Route::prefix('smp')->group(function () {
    Route::middleware([
        SubstituteBindings::class,
        'auth:sanctum',
    ])->group(function () {
        Route::get('/vendors', [ VendorsController::class, 'getVendors' ]);
        Route::post('/vendors/create', [ VendorsController::class, 'storeVendor' ]);
        Route::get('/reports/vendor-report', [ VendorReportsController::class, 'getVendorReport' ]);
        Route::get('/reports/vendor-sale-report', [ VendorReportsController::class, 'getVendorSaleReport' ]);
    });
});