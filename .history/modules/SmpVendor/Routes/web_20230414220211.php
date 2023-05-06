<?php

use Modules\SmpVendor\Http\Controllers\VendorsController;
use Modules\SmpVendor\Http\Controllers\VendorReportsController;
use Illuminate\Support\Facades\Route;

Route::get('/vendors', [ VendorsController::class, 'listUsers' ])->name('ns.dashboard.users')->middleware([ 'ns.restrict:read.users' ]);
Route::get('/users/create', [ VendorsController::class, 'createUser' ])->name('ns.dashboard.users-create');
Route::get('/users/edit/{user}', [ VendorsController::class, 'editUser' ])->name('ns.dashboard.users.edit');
