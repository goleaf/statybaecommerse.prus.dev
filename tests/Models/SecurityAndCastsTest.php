<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

if (! function_exists('classExistsInAppModels')) {
    /**
     * Determine if the given fully-qualified class name belongs to the App\\Models namespace.
     */
    function classExistsInAppModels(string $class): bool
    {
        // Guard the helper so test discovery remains resilient even if a suggested model does not exist yet.
        return class_exists($class) && str_starts_with($class, 'App\\Models\\');
    }
}

dataset('userLikeModels', [
    'App\\Models\\User',
    'App\\Models\\AdminUser',
]);

it('user-like models hide sensitive columns and hash passwords when persisted', function (string $fqcn): void {
    if (! classExistsInAppModels($fqcn)) {
        // Skip gracefully when a project variant omits a recommended authentication model.
        markTestSkipped("$fqcn not present.");
    }

    /** @var \Illuminate\Database\Eloquent\Model $model */
    $model = new $fqcn;

    // Fetch the hidden attribute list without triggering accessor side effects.
    $hidden = property_exists($model, 'hidden') ? $model->getHidden() : [];

    expect($hidden)->toContain('password');

    // Only assert on remember_token visibility when the attribute is actually part of the model payload.
    if (array_key_exists('remember_token', $model->getAttributes()) || property_exists($model, 'remember_token')) {
        expect($hidden)->toContain('remember_token');
    }

    $casts = method_exists($model, 'getCasts') ? $model->getCasts() : [];

    // Laravel 12 recommends the dedicated "hashed" cast to avoid double hashing or plain text persistence.
    expect(array_key_exists('password', $casts) && $casts['password'] === 'hashed')
        ->toBeTrue('Expected password cast "hashed" on ' . $fqcn);
})->with('userLikeModels');
