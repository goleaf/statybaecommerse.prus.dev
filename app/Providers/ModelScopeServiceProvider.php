<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Throwable;

final class ModelScopeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $scopesByModel = $this->buildModelScopeMap();

        foreach ($scopesByModel as $modelClass => $scopeClasses) {
            try {
                $isModel = class_exists($modelClass) && is_subclass_of($modelClass, Model::class);
            } catch (Throwable) {
                // Skip models that cannot be autoloaded cleanly (e.g. due to parse errors in vendor stubs).
                continue;
            }

            if (! $isModel) {
                continue;
            }

            foreach (array_unique($scopeClasses) as $scopeClass) {
                try {
                    if (! class_exists($scopeClass)) {
                        continue;
                    }
                } catch (Throwable) {
                    // Skip problematic scope classes without stopping the application bootstrap.
                    continue;
                }

                try {
                    $modelClass::addGlobalScope(new $scopeClass);
                } catch (Throwable) {
                    // Avoid bubbling issues from unexpected constructor signatures or other runtime failures.
                    continue;
                }
            }
        }
    }

    /**
     * @return array<class-string<Model>, list<class-string>>
     */
    private function buildModelScopeMap(): array
    {
        $scopeConfiguration = config('model-scopes', []);
        $modelScopes = [];

        foreach ($scopeConfiguration as $scopeClass => $models) {
            foreach ($models as $modelClass) {
                $modelScopes[$modelClass] ??= [];
                $modelScopes[$modelClass][] = $scopeClass;
            }
        }

        return $modelScopes;
    }
}
