<?php

use Modules\SmpVendor\Http\Controllers\VendorsController;
use App\Http\Middleware\ClearRequestCacheMiddleware;
use App\Http\Middleware\InstalledStateMiddleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

Route::prefix( 'nexopos/v4' )->group( function() {
        Route::middleware([
            SubstituteBindings::class,
            'auth:sanctum',
        ])->group( function() {
    Route::get('/vendors', [ VendorsController::class, 'getVendors' ]);
    Route::put('/users/roles', [ UsersController::class, 'updateRole' ]);
    Route::get('/users/roles/{role}/clone', [ UsersController::class, 'cloneRole' ]);
    Route::get('/users/permissions', [ UsersController::class, 'getPermissions' ]);
})
});