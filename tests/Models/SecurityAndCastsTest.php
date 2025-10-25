<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/**
 * Lightweight helper to confirm an application model class exists before running assertions.
 */
function modelClassExists(string $class): bool
{
    return class_exists($class) && is_subclass_of($class, Model::class);
}

/**
 * Determine whether the underlying table exposes a given column so we can skip optional checks safely.
 */
function modelHasColumn(Model $model, string $column): bool
{
    $table = $model->getTable();

    return Schema::hasTable($table) && Schema::hasColumn($table, $column);
}

dataset('userLikeModels', [
    \App\Models\User::class,
    \App\Models\AdminUser::class,
]);

it('user-like models hide sensitive fields and hash passwords automatically', function (string $fqcn): void {
    if (! modelClassExists($fqcn)) {
        test()->skip($fqcn . ' not present.');
    }

    /** @var Model $model */
    $model = new $fqcn;

    /** @var array<int, string> $hidden */
    $hidden = $model->getHidden();

    // Only require the password column to be hidden when the table actually contains it.
    if (modelHasColumn($model, 'password')) {
        expect($hidden)->toContain('password');
    }

    // Remember tokens are optional, so conditionally assert when the column exists.
    if (modelHasColumn($model, 'remember_token')) {
        expect($hidden)->toContain('remember_token');
    }

    /** @var array<string, string> $casts */
    $casts = $model->getCasts();

    // The modern Laravel guidance is to rely on the built-in "hashed" cast for passwords.
    if (modelHasColumn($model, 'password')) {
        expect($casts['password'] ?? null)
            ->toBe('hashed', 'Expected password cast "hashed" on ' . $fqcn);
    }
})->with('userLikeModels');
