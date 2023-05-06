<?php

use Modules\SmpVendor\Http\Controllers\VendorsController;
use Modules\SmpVendor\Http\Controllers\VendorReportsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Middleware\SubstituteBindings;
use App\Http\Middleware\Authenticate;

Route::prefix('dashboard')->group(function () {
    Route::middleware([
        SubstituteBindings::class,
        Authenticate::class,
    ])->group(function () {
        Route::get('/vendors', [ VendorsController::class, 'listVendors' ])->name('smp.vendors')->middleware([ 'ns.restrict:read.vendors' ]);
        Route::get('/vendors/create', [ VendorsController::class, 'createVendor' ])->name('smp.vendors-create');
        Route::get('/vendors/edit/{vendor}', [ VendorsController::class, 'editVendor' ])->name('ns.smp.vendors.edit');
        Route::get('/reports/vendors-monthly-statement-report', [ VendorReportsController::class, 'showVendorMonthlyStatement' ])->name(ns()->routeName('smp.reports.vendors-monthly-statement'));
    });
});
