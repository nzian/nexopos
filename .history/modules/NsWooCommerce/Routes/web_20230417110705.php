<?php

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Modules\NsWooCommerce\Http\Controllers\DashboardController;
use Modules\NsWooCommerce\Http\Controllers\WooCommerceController;
use Modules\NsWooCommerce\Http\Middleware\VerifyWebhook;

Route::prefix('dashboard')
    ->middleware([SubstituteBindings::class])
    ->group(function () {
        Route::get('/settings/nsw.settings-page', [DashboardController::class, 'settings'])
            ->name(ns()->routeName('nsw.settings-page'));
    });

Route::prefix('webhook')
    ->middleware([SubstituteBindings::class, VerifyWebhook::class])
    ->group(function () {
        Route::post('wc', [WooCommerceController::class, 'listenEvents']);
    });