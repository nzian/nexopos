<?php

use Modules\SmpVendor\Http\Controllers\VendorsController;
use Modules\SmpVendor\Http\Controllers\VendorReportsController;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

Route::prefix('nexopos/v4')->group(function () {
    Route::middleware([
        SubstituteBindings::class,
        'auth:sanctum',
    ])->group(function () {
        Route::get('/vendors', [ VendorsController::class, 'getVendors' ]);
        Route::post('/vendors/store', [ VendorsController::class, 'storeVendor' ]);
        Route::get('/reports/vendor-report', [ VendorReportsController::class, 'getVendorReport' ]);
    });
});
