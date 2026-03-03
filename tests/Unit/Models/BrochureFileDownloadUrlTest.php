<?php

declare(strict_types=1);

use App\Models\BrochureFile;

it('generates signed brochure download url without expiration', function (): void {
    $file = new BrochureFile([
        'file_path' => 'brochures/test-file.pdf',
    ]);

    $url = $file->downloadUrl();

    expect($url)->toContain('download=1');
    expect($url)->toContain('signature=');
    expect($url)->not()->toContain('expires=');
});
