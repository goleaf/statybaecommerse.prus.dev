<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
 * |--------------------------------------------------------------------------
 * | Admin Routes
 * |--------------------------------------------------------------------------
 * |
 * | Here is where you can register admin routes for your application.
 * | These routes are loaded by the RouteServiceProvider and all of them will
 * | be assigned to the "web" middleware group.
 * |
 */

Route::middleware(['web', 'auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Analytics
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('index');
        Route::get('/reports', [App\Http\Controllers\Admin\AnalyticsController::class, 'reports'])->name('reports');
        Route::get('/export', [App\Http\Controllers\Admin\AnalyticsController::class, 'export'])->name('export');
        Route::get('/dashboard', [App\Http\Controllers\Admin\AnalyticsController::class, 'dashboard'])->name('dashboard');
    });

    // System Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('index');
        Route::post('/update', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('update');
        Route::get('/cache/clear', [App\Http\Controllers\Admin\SettingsController::class, 'clearCache'])->name('cache.clear');
        Route::get('/queue/restart', [App\Http\Controllers\Admin\SettingsController::class, 'restartQueue'])->name('queue.restart');
    });

    // Enum Management
    Route::prefix('enums')->name('enums.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\EnumController::class, 'index'])->name('index');
        Route::get('/types', [App\Http\Controllers\Admin\EnumController::class, 'types'])->name('types');
        Route::get('/values/{type}', [App\Http\Controllers\Admin\EnumController::class, 'values'])->name('values');
        Route::post('/import', [App\Http\Controllers\Admin\EnumController::class, 'import'])->name('import');
        Route::get('/export', [App\Http\Controllers\Admin\EnumController::class, 'export'])->name('export');
    });

    // Bulk Operations
    Route::prefix('bulk')->name('bulk.')->group(function () {
        Route::post('/products/import', [App\Http\Controllers\Admin\BulkController::class, 'importProducts'])->name('products.import');
        Route::post('/products/export', [App\Http\Controllers\Admin\BulkController::class, 'exportProducts'])->name('products.export');
        Route::post('/orders/export', [App\Http\Controllers\Admin\BulkController::class, 'exportOrders'])->name('orders.export');
        Route::post('/users/export', [App\Http\Controllers\Admin\BulkController::class, 'exportUsers'])->name('users.export');
        Route::post('/campaigns/export', [App\Http\Controllers\Admin\BulkController::class, 'exportCampaigns'])->name('campaigns.export');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
        Route::get('/sales', [App\Http\Controllers\Admin\ReportController::class, 'sales'])->name('sales');
        Route::get('/products', [App\Http\Controllers\Admin\ReportController::class, 'products'])->name('products');
        Route::get('/customers', [App\Http\Controllers\Admin\ReportController::class, 'customers'])->name('customers');
        Route::get('/campaigns', [App\Http\Controllers\Admin\ReportController::class, 'campaigns'])->name('campaigns');
        Route::get('/inventory', [App\Http\Controllers\Admin\ReportController::class, 'inventory'])->name('inventory');
        Route::get('/export/{type}', [App\Http\Controllers\Admin\ReportController::class, 'export'])->name('export');
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('index');
        Route::post('/send', [App\Http\Controllers\Admin\NotificationController::class, 'send'])->name('send');
        Route::post('/mark-read', [App\Http\Controllers\Admin\NotificationController::class, 'markRead'])->name('mark-read');
        Route::delete('/clear', [App\Http\Controllers\Admin\NotificationController::class, 'clear'])->name('clear');
    });

    // Activity Logs
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\LogController::class, 'index'])->name('index');
        Route::get('/activity', [App\Http\Controllers\Admin\LogController::class, 'activity'])->name('activity');
        Route::get('/errors', [App\Http\Controllers\Admin\LogController::class, 'errors'])->name('errors');
        Route::get('/download/{log}', [App\Http\Controllers\Admin\LogController::class, 'download'])->name('download');
        Route::delete('/clear', [App\Http\Controllers\Admin\LogController::class, 'clear'])->name('clear');
    });

    // Backup & Restore
    Route::prefix('backup')->name('backup.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\BackupController::class, 'index'])->name('index');
        Route::post('/create', [App\Http\Controllers\Admin\BackupController::class, 'create'])->name('create');
        Route::get('/download/{backup}', [App\Http\Controllers\Admin\BackupController::class, 'download'])->name('download');
        Route::delete('/delete/{backup}', [App\Http\Controllers\Admin\BackupController::class, 'delete'])->name('delete');
        Route::post('/restore/{backup}', [App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('restore');
    });

    // API Routes for AJAX
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/stats', [App\Http\Controllers\Admin\ApiController::class, 'stats'])->name('stats');
        Route::get('/charts/sales', [App\Http\Controllers\Admin\ApiController::class, 'salesChart'])->name('charts.sales');
        Route::get('/charts/products', [App\Http\Controllers\Admin\ApiController::class, 'productsChart'])->name('charts.products');
        Route::get('/charts/customers', [App\Http\Controllers\Admin\ApiController::class, 'customersChart'])->name('charts.customers');
        Route::get('/search/{type}', [App\Http\Controllers\Admin\ApiController::class, 'search'])->name('search');
        Route::post('/bulk-action', [App\Http\Controllers\Admin\ApiController::class, 'bulkAction'])->name('bulk-action');
    });

    // File Management
    Route::prefix('files')->name('files.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\FileController::class, 'index'])->name('index');
        Route::post('/upload', [App\Http\Controllers\Admin\FileController::class, 'upload'])->name('upload');
        Route::delete('/delete/{file}', [App\Http\Controllers\Admin\FileController::class, 'delete'])->name('delete');
        Route::get('/download/{file}', [App\Http\Controllers\Admin\FileController::class, 'download'])->name('download');
    });

    // Email Templates
    Route::prefix('email-templates')->name('email-templates.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\EmailTemplateController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\EmailTemplateController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\EmailTemplateController::class, 'store'])->name('store');
        Route::get('/{template}', [App\Http\Controllers\Admin\EmailTemplateController::class, 'show'])->name('show');
        Route::get('/{template}/edit', [App\Http\Controllers\Admin\EmailTemplateController::class, 'edit'])->name('edit');
        Route::put('/{template}', [App\Http\Controllers\Admin\EmailTemplateController::class, 'update'])->name('update');
        Route::delete('/{template}', [App\Http\Controllers\Admin\EmailTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/{template}/test', [App\Http\Controllers\Admin\EmailTemplateController::class, 'test'])->name('test');
    });

    // System Health
    Route::prefix('health')->name('health.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\HealthController::class, 'index'])->name('index');
        Route::get('/database', [App\Http\Controllers\Admin\HealthController::class, 'database'])->name('database');
        Route::get('/cache', [App\Http\Controllers\Admin\HealthController::class, 'cache'])->name('cache');
        Route::get('/queue', [App\Http\Controllers\Admin\HealthController::class, 'queue'])->name('queue');
        Route::get('/storage', [App\Http\Controllers\Admin\HealthController::class, 'storage'])->name('storage');
    });

    // Maintenance
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\MaintenanceController::class, 'index'])->name('index');
        Route::post('/optimize', [App\Http\Controllers\Admin\MaintenanceController::class, 'optimize'])->name('optimize');
        Route::post('/migrate', [App\Http\Controllers\Admin\MaintenanceController::class, 'migrate'])->name('migrate');
        Route::post('/seed', [App\Http\Controllers\Admin\MaintenanceController::class, 'seed'])->name('seed');
        Route::post('/clear-cache', [App\Http\Controllers\Admin\MaintenanceController::class, 'clearCache'])->name('clear-cache');
        Route::post('/clear-logs', [App\Http\Controllers\Admin\MaintenanceController::class, 'clearLogs'])->name('clear-logs');
    });
});
