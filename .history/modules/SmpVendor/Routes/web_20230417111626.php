<?php

use Modules\SmpVendor\Http\Controllers\VendorsController;
use Modules\SmpVendor\Http\Controllers\VendorReportsController;
use Illuminate\Support\Facades\Route;

Route::get('/vendors', [ VendorsController::class, 'listVendors' ])->name('smp.vendors')->middleware([ 'ns.restrict:read.vendors' ]);
Route::get('/vendors/create', [ VendorsController::class, 'createVendor' ])->name('smp.vendors-create');
Route::get('/vendors/edit/{vendor}', [ VendorsController::class, 'editVendor' ])->name('ns.smp.vendors.edit');
Route::get('/reports/vendors-monthly-statement-report', [ VendorReportsController::class, 'showVendorMonthlyStatement' ])->name(ns()->routeName('smp.reports.vendors-monthly-statement'));
