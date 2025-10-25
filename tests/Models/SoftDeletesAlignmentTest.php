<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/**
 * Discover application models by scanning the default app/Models directory.
 * Extend this helper if your project stores models in nested namespaces.
 *
 * @return list<class-string<Model>>
 */
function discoverAppModels(string $dir = 'app/Models'): array
{
    if (! File::isDirectory(base_path($dir))) {
        return [];
    }

    $classes = [];

    foreach (File::allFiles(base_path($dir)) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $class = 'App\\Models\\' . $file->getFilenameWithoutExtension();

        if (class_exists($class) && is_subclass_of($class, Model::class)) {
            $classes[] = $class;
        }
    }

    return $classes;
}

it('ensures models with deleted_at columns opt-in to SoftDeletes', function (): void {
    foreach (discoverAppModels() as $class) {
        try {
            /** @var Model $model */
            $model = app($class);
        } catch (Throwable) {
            // Skip abstract or stateful models that cannot be instantiated without dependencies.
            continue;
        }

        $table = $model->getTable();

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'deleted_at')) {
            // No soft delete column defined for this model's table, so there is nothing to assert.
            continue;
        }

        $uses = class_uses_recursive($class);

        expect(in_array(SoftDeletes::class, $uses, true))
            ->toBeTrue($class . " uses table '{$table}' with deleted_at but does not use SoftDeletes.");
    }

    // Final assertion keeps Pest from flagging the test as risky when every iteration continues.
    expect(true)->toBeTrue();
});
