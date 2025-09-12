<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\CodeStyleService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

final class CodeStyleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CodeStyleService::class);
    }

    public function boot(): void
    {
        if ($this->app->environment('local', 'testing')) {
            $this->registerAutoFix();
        }
    }

    private function registerAutoFix(): void
    {
        // Auto-fix on file save (development only)
        Event::listen('file.saved', function (string $filePath) {
            if ($this->shouldAutoFix($filePath)) {
                $codeStyleService = app(CodeStyleService::class);
                $fixes = $codeStyleService->fixFile($filePath);

                if (! empty($fixes)) {
                    logger()->info("Auto-fixed code style issues in {$filePath}", $fixes);
                }
            }
        });
    }

    private function shouldAutoFix(string $filePath): bool
    {
        // Only auto-fix PHP files in app/ directory
        return str_ends_with($filePath, '.php') &&
               str_starts_with($filePath, app_path());
    }
}
