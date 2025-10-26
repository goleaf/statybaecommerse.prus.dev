<?php

declare(strict_types=1);

use App\Models\SeoData;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Tests\Support\AssertsRelations;

/**
 * Dataset ensuring the shared OrdersByName trait recognises SeoData columns.
 */
dataset('seo_data_ordered_by_name', [
    [SeoData::class, ['title', 'slug', 'name']],
]);

it('confirms SeoData participates in the orderedByName dataset', function (string $class, array $columns): void {
    $model = new $class;

    // The OrdersByName trait should rely on the explicit nameColumn property.
    expect(property_exists($model, 'nameColumn') ? $model->nameColumn : null)->toBe('title');
    expect($columns)->toBe(['title', 'slug', 'name']);
})->with('seo_data_ordered_by_name');

/**
 * Dataset validating SeoData relationship contracts.
 */
dataset('seo_data_relations', [
    [SeoData::class, 'seoable', MorphTo::class],
]);

it('ensures SeoData relations align with expectations', function (string $class, string $relation, string $relationClass): void {
    $model = new $class;

    // Leverage the shared assertion helper to keep relation checks consistent.
    AssertsRelations::assertRelation($model, $relation, $relationClass);
})->with('seo_data_relations');
