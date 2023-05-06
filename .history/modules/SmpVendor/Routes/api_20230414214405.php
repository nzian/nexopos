<?php

use Modules\SmpVendor\Http\Controllers\VendorsController;
use Illuminate\Support\Facades\Route;

Route::prefix( 'nexopos/v4' )->group( function() {
    Route::middleware([
        InstalledStateMiddleware::class,
        SubstituteBindings::class,
        ClearRequestCacheMiddleware::class,
    ])->group( function() {
        include dirname( __FILE__ ) . '/api/fields.php';

        Route::middleware([
            'auth:sanctum',
        ])->group( function() {
    Route::get('/vendors', [ VendorsController::class, 'getVendors' ]);
    Route::put('/users/roles', [ UsersController::class, 'updateRole' ]);
    Route::get('/users/roles/{role}/clone', [ UsersController::class, 'cloneRole' ]);
    Route::get('/users/permissions', [ UsersController::class, 'getPermissions' ]);
})
    });
});