<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\ImportProducts::class,
                \App\Console\Commands\ImportPrices::class,
                \App\Console\Commands\ImportInventory::class,
            ]);
        }
    }

    public function boot(): void
    {
        // Ensure Shopper brand form alias is registered even if package config merge order changes
        if (class_exists(\Shop\Livewire\SlideOvers\BrandForm::class)) {
            Livewire::component('shopper-slide-overs.brand-form', \Shop\Livewire\SlideOvers\BrandForm::class);
        }

        Model::saved(function ($model): void {
            $this->flushSitemapIfCatalog($model);
            $this->flushDiscountsIfNeeded($model);
        });
        Model::deleted(function ($model): void {
            $this->flushSitemapIfCatalog($model);
            $this->flushDiscountsIfNeeded($model);
        });

        $this->app->afterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command('demo:prices-multi-safe')->dailyAt('02:10')->withoutOverlapping();
            $schedule->command('demo:export-csv')->dailyAt('02:25')->withoutOverlapping();
            // Optional nightly imports if files exist
            $schedule->call(function (): void {
                $base = storage_path('app/import');
                $map = [
                    'products.csv' => 'import:products',
                    'prices.csv' => 'import:prices',
                    'inventory.csv' => 'import:inventory',
                ];
                foreach ($map as $file => $cmd) {
                    $path = $base . DIRECTORY_SEPARATOR . $file;
                    if (is_file($path)) {
                        \Artisan::call($cmd, ['path' => $path, '--chunk' => 500]);
                    }
                }
            })->dailyAt('03:00')->name('imports:nightly')->withoutOverlapping();
            $schedule->call(function (): void {
                // Rotate exports older than 7 days
                $disk = \Storage::disk('public');
                $dir = 'exports';
                if ($disk->exists($dir)) {
                    foreach ($disk->files($dir) as $path) {
                        $lastModified = $disk->lastModified($path);
                        if ($lastModified && $lastModified < now()->subDays(7)->getTimestamp()) {
                            $disk->delete($path);
                        }
                    }
                }
            })->dailyAt('02:40')->name('exports:rotate')->withoutOverlapping();
        });

        // Use localized Markdown templates for auth notifications
        ResetPassword::toMailUsing(function ($notifiable, string $url) {
            $locale = method_exists($notifiable, 'preferredLocale') ? ($notifiable->preferredLocale() ?: app()->getLocale()) : app()->getLocale();
            $minutes = (int) config('auth.passwords.' . config('auth.defaults.passwords') . '.expire');

            return (new MailMessage)
                ->locale($locale)
                ->subject(__('mail.reset_password_subject', [], $locale))
                ->markdown('emails.auth.password-reset', [
                    'url' => $url,
                    'minutes' => $minutes,
                ]);
        });

        VerifyEmail::toMailUsing(function ($notifiable, string $url) {
            $locale = method_exists($notifiable, 'preferredLocale') ? ($notifiable->preferredLocale() ?: app()->getLocale()) : app()->getLocale();

            return (new MailMessage)
                ->locale($locale)
                ->subject(__('mail.verify_email_subject', [], $locale))
                ->markdown('emails.auth.verify', [
                    'url' => $url,
                ]);
        });
    }

    private function flushSitemapIfCatalog($model): void
    {
        $classes = [
            \App\Models\Product::class,
            \App\Models\Brand::class,
            \App\Models\Category::class,
            \App\Models\Collection::class,
        ];
        foreach ($classes as $class) {
            if ($model instanceof $class) {
                $locales = collect(config('app.supported_locales', 'en'))
                    ->when(fn($v) => is_string($v), fn($c) => collect(explode(',', (string) $c)))
                    ->map(fn($v) => trim((string) $v))
                    ->filter()
                    ->values();
                foreach ($locales as $loc) {
                    Cache::forget("sitemap:urls:{$loc}");
                }
                break;
            }
        }
    }

    private function flushDiscountsIfNeeded($model): void
    {
        if ($model instanceof \Shop\Core\Models\Discount ||
                $model instanceof \Shop\Core\Models\DiscountCode ||
                $model instanceof \Shop\Core\Models\DiscountCondition) {
            try {
                Cache::tags(['discounts'])->flush();
            } catch (\Throwable $e) {
            }
        }
    }
}
