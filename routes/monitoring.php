<?php

declare(strict_types=1);

use App\Http\Controllers\Monitoring\MetricsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:60,1'])
    ->get('/metrics', MetricsController::class)
    ->name('monitoring.metrics');
