<?php

declare(strict_types=1);

namespace App\Support\Filesystem;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for enhanced filesystem services.
 */
final class FilesystemServiceProvider extends ServiceProvider
{
    /**
     * Register filesystem services.
     */
    public function register(): void
    {
        $this->app->singleton(FilesystemPermissions::class, static function (): FilesystemPermissions {
            return config('app.env') === 'production'
                ? FilesystemPermissions::secure()
                : FilesystemPermissions::default();
        });

        $this->app->singleton(DirectoryMemoryManager::class);

        $this->app->singleton(BackupDatabaseManager::class, static function ($app): BackupDatabaseManager {
            return new BackupDatabaseManager($app->make(FilesystemPermissions::class));
        });

        $this->app->singleton(DirectoryScanner::class, static function ($app): DirectoryScanner {
            return new DirectoryScanner(
                $app->make(Filesystem::class),
                $app->make(DirectoryMemoryManager::class)
            );
        });

        // Replace the default filesystem binding with the graceful implementation
        $this->app->singleton(Filesystem::class, static function ($app): Filesystem {
            return new GracefulFilesystem(
                $app->make(FilesystemPermissions::class),
                $app->make(DirectoryMemoryManager::class),
                $app->make(BackupDatabaseManager::class),
                $app->make(DirectoryScanner::class)
            );
        });

        $this->app->alias(Filesystem::class, 'files');
    }

    /**
     * Bootstrap filesystem services.
     */
    public function boot(): void
    {
        // Register filesystem configuration if needed
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/filesystem.php' => config_path('filesystem-enhanced.php'),
            ], 'filesystem-config');
        }
    }
}
