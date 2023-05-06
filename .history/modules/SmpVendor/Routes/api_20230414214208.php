<?php

use Modules\SmpVendor\Http\Controllers\VendorsController;
use Illuminate\Support\Facades\Route;

Route::get('/vendors', [ VendorsController::class, 'getVendors' ]);
Route::get
Route::put('/users/roles', [ UsersController::class, 'updateRole' ]);
Route::get('/users/roles/{role}/clone', [ UsersController::class, 'cloneRole' ]);
Route::get('/users/permissions', [ UsersController::class, 'getPermissions' ]);