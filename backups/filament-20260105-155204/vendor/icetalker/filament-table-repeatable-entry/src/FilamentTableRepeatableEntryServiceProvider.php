<?php

namespace Icetalker\FilamentTableRepeatableEntry;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;

class FilamentTableRepeatableEntryServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('filament-table-repeatable-entry')
            ->hasViews();
    }

    public function boot()
    {
        $this->registerAssets();

        $this->bootLoaders();
    }

    protected function bootLoaders()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-table-repeatable-entry');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'filament-table-repeatable-entry');
    }

    protected function registerAssets()
    {
        FilamentAsset::register([
            Css::make('table-repeatable-entry', __DIR__ .'/../dist/css/table-repeatable-entry.css')
        ], 'filament-table-repeatable-entry');
    }

}
