<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Partner\OrdersIndexController;
use App\Http\Controllers\Api\Partner\PingController;
use Illuminate\Support\Facades\Route;

Route::get('ping', PingController::class)
    ->name('ping');

Route::get('orders', OrdersIndexController::class)
    ->middleware('partner.api.scope:orders.read')
    ->name('orders.index');
