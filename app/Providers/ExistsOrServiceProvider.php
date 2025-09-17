<?php declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

final class ExistsOrServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register existsOr macro for Builder
        Builder::macro('existsOr', function (callable $existsCallback, ?callable $notExistsCallback = null): bool {
            $exists = $this->exists();
            
            if ($exists) {
                $existsCallback();
            } elseif ($notExistsCallback) {
                $notExistsCallback();
            }
            
            return $exists;
        });

        // Register existsOr macro for Relation
        Relation::macro('existsOr', function (callable $existsCallback, ?callable $notExistsCallback = null): bool {
            $exists = $this->exists();
            
            if ($exists) {
                $existsCallback();
            } elseif ($notExistsCallback) {
                $notExistsCallback();
            }
            
            return $exists;
        });
    }

    public function register(): void
    {
        //
    }
}
