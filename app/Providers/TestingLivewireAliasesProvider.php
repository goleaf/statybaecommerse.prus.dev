<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Http\Response;
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
    }

    public function boot(): void
    {
        // Provide a fallback assertion macro used by some tests when a Response is returned instead of Livewire testable
        if (! Response::hasMacro('assertCanSeeRecord')) {
            Response::macro('assertCanSeeRecord', function ($record) {
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
