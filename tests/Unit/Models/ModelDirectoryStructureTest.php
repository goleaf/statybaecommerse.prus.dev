<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;

it('keeps app/Models PHP files alphabetically sorted and model-only', function (): void {
    $directory = app_path('Models');

    // glob() already yields results sorted alphabetically, which keeps the expectation consistent
    // across filesystems that may otherwise return directory entries in hash order (e.g. ext4).
    $filenames = collect(glob($directory . '/*.php'))
        ->map(basename(...))
        ->values();

    $sorted = $filenames->sort()->values();

    expect($filenames->all())
        ->toBe($sorted->all());

    $filenames->each(function (string $filename): void {
        $class = 'App\\Models\\' . str_replace('.php', '', $filename);

        expect(class_exists($class))
            ->toBeTrue()
            // Guard against stray helpers sneaking into the Models folder by requiring each class to extend Eloquent.
            ->and(is_subclass_of($class, Model::class))
            ->toBeTrue();
    });
});
