<?php

declare(strict_types=1);

it('unit: media library temporary upload model is a string to avoid hard dependency on pro package', function (): void {
    $value = config('media-library.temporary_upload_model');
    expect($value)->toBeString()->toBe('Spatie\\MediaLibraryPro\\Models\\TemporaryUpload');
});

