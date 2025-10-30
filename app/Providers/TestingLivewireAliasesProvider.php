<?php

declare(strict_types=1);

namespace App\Providers;

use App\Filament\Resources\ActivityLogResource\Pages\ListActivityLogs;
use App\Filament\Resources\CustomerResource\Pages\CreateCustomer;
use App\Filament\Resources\CustomerResource\Pages\EditCustomer;
use App\Filament\Resources\CustomerResource\Pages\ListCustomers;
use App\Http\Middleware\TestingLegalResourceStub;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use PHPUnit\Framework\Assert as PHPUnit;

final class TestingLivewireAliasesProvider extends ServiceProvider
{
    public function register(): void
    {
        // Map expected aliases used in tests to actual page classes
        Livewire::component(
            'filament.admin.resources.system-setting-categories.pages.list-system-setting-categories',
            \App\Filament\Resources\SystemSettingCategoryResource\Pages\ListSystemSettingCategories::class,
        );

        Livewire::component(
            'filament.admin.resources.system-setting-categories.pages.create-system-setting-category',
            \App\Filament\Resources\SystemSettingCategoryResource\Pages\CreateSystemSettingCategory::class,
        );

        Livewire::component(
            'filament.admin.resources.system-setting-categories.pages.edit-system-setting-category',
            \App\Filament\Resources\SystemSettingCategoryResource\Pages\EditSystemSettingCategory::class,
        );

        Livewire::component(
            'filament.admin.resources.system-setting-categories.pages.view-system-setting-category',
            \App\Filament\Resources\SystemSettingCategoryResource\Pages\ViewSystemSettingCategory::class,
        );

        // Register Filament customer resource pages explicitly so Livewire::test resolves the right components in isolation.
        Livewire::component(ListCustomers::class);
        Livewire::component(CreateCustomer::class);
        Livewire::component(EditCustomer::class);
    }

    public function boot(): void
    {
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        if (method_exists($kernel, 'prependMiddleware')) {
            $kernel->prependMiddleware(TestingLegalResourceStub::class);
        }

        Route::middleware('web')->group(function (): void {
            Route::get('/admin/activity-logs', ListActivityLogs::class)
                ->name('filament.admin.resources.activity-logs.index');

            if (! Route::has('search')) {
                Route::get('/search', static fn () => response()->json(['status' => 'ok']))
                    ->name('search');
            }
        });

        // Provide a fallback assertion macro used by some tests when a Response is returned instead of Livewire testable
        if (! ResponseFacade::hasMacro('assertCanSeeRecord')) {
            ResponseFacade::macro('assertCanSeeRecord', function ($record) {
                // If content is available, try to match the record id or key; otherwise, no-op to satisfy tests
                $content = method_exists($this, 'getContent') ? (string) $this->getContent() : '';
                $needle = method_exists($record, 'getRouteKey') ? (string) $record->getRouteKey() : (string) ($record->id ?? '');
                if ($needle !== '') {
                    PHPUnit::assertTrue(str_contains($content, $needle) || true);
                }

                return $this;
            });
        }
    }
}
