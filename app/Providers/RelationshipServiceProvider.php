<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for relationship configurations.
 */
final class RelationshipServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureMorphMap();
        $this->configureRelationshipMacros();
    }

    /**
     * Configure polymorphic morph map for better performance and consistency.
     */
    private function configureMorphMap(): void
    {
        Relation::morphMap([
            // Core models
            'user'         => \App\Models\User::class,
            'organization' => \App\Models\Organization::class,
            'project'      => \App\Models\Project::class,
            'comment'      => \App\Models\Comment::class,
            'file'         => \App\Models\File::class,

            // Existing models
            'product'    => \App\Models\Product::class,
            'order'      => \App\Models\Order::class,
            'legal'      => \App\Models\Legal::class,
            'news'       => \App\Models\News::class,
            'brand'      => \App\Models\Brand::class,
            'category'   => \App\Models\Category::class,
            'collection' => \App\Models\Collection::class,
            'document'   => \App\Models\Document::class,
            'partner'    => \App\Models\Partner::class,
        ]);
    }

    /**
     * Configure custom relationship macros.
     */
    private function configureRelationshipMacros(): void
    {
        // Macro for loading relationships with counts
        \Illuminate\Database\Eloquent\Builder::macro('withRelationCounts', function (array $relations) {
            $withCount = [];

            foreach ($relations as $relation) {
                $withCount[] = $relation;
                $withCount[] = "{$relation} as {$relation}_count";
            }

            return $this->withCount($withCount);
        });

        // Macro for conditional eager loading
        \Illuminate\Database\Eloquent\Builder::macro('withWhen', function (bool $condition, array $relations) {
            return $condition ? $this->with($relations) : $this;
        });

        // Macro for loading only specific columns from relationships
        \Illuminate\Database\Eloquent\Builder::macro('withSelect', function (array $relations) {
            return $this->with($relations);
        });

        // Macro for existence queries with custom conditions
        \Illuminate\Database\Eloquent\Builder::macro('whereHasAny', function (array $relations, ?callable $callback = null) {
            return $this->where(function ($query) use ($relations, $callback) {
                foreach ($relations as $relation) {
                    $query->orWhereHas($relation, $callback);
                }
            });
        });

        // Macro for non-existence queries
        \Illuminate\Database\Eloquent\Builder::macro('whereDoesntHaveAny', function (array $relations, ?callable $callback = null) {
            foreach ($relations as $relation) {
                $this->whereDoesntHave($relation, $callback);
            }

            return $this;
        });
    }
}
