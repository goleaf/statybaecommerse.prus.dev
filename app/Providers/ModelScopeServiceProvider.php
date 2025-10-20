<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

final class ModelScopeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $scopesByModel = $this->buildModelScopeMap();

        foreach ($scopesByModel as $modelClass => $scopeClasses) {
            if (! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
                continue;
            }

            foreach (array_unique($scopeClasses) as $scopeClass) {
                if (! class_exists($scopeClass)) {
                    continue;
                }

                $modelClass::addGlobalScope(new $scopeClass());
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
