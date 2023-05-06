<?php

use Modules\SmpVendor\Http\Controllers\VendorsController;
use Modules\SmpVendor\Http\Controllers\VendorReportsController;
use Illuminate\Support\Facades\Route;

Route::get('/vendors', [ VendorsController::class, 'listVendors' ])->name('ns.smp.vendors')->middleware([ 'ns.restrict:read.vendors' ]);
Route::get('/vendors/create', [ VendorsController::class, 'createVendor' ])->name('ns.smp.vendors-create');
Route::get('/vendors/edit/{vendor}', [ VendorsController::class, 'editVendor' ])->name('ns.smp.vendors.edit');
