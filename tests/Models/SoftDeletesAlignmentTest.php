<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

if (! function_exists('discoverModelClasses')) {
    /**
     * Discover application model classes within the default app/Models directory.
     *
     * @return list<class-string<Model>>
     */
    function discoverModelClasses(string $dir = 'app/Models'): array
    {
        // Bail out early when the project layout diverges from the conventional models directory.
        if (! File::isDirectory(base_path($dir))) {
            return [];
        }

        $classes = [];

        foreach (File::allFiles(base_path($dir)) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $class = 'App\\Models\\' . $file->getFilenameWithoutExtension();

            if (class_exists($class)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }
}

it('requires SoftDeletes on models whose tables expose a deleted_at column', function (): void {
    foreach (discoverModelClasses() as $class) {
        try {
            /** @var Model $instance */
            $instance = new $class;
        } catch (\Throwable $exception) {
            // Skip abstract classes or read-only models that cannot be instantiated normally.
            continue;
        }

        $table = $instance->getTable();

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'deleted_at')) {
            // Tables without a deleted_at column are not expected to implement SoftDeletes.
            continue;
        }

        $usesSoftDeletes = in_array(SoftDeletes::class, class_uses_recursive($class), true);

        expect($usesSoftDeletes)->toBeTrue(
            sprintf('%s uses table %s with deleted_at but does not use SoftDeletes.', $class, $table)
        );
    }

    // Reach the assertion phase even when all checks are skipped above.
    expect(true)->toBeTrue();
});
