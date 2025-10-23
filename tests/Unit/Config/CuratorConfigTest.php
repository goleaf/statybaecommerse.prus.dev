<?php

declare(strict_types=1);

it('unit: curator config uses string class names for optional package', function (): void {
    $config = config('curator');

    expect($config['curation_presets'][0])->toBeString();
    expect($config['glide']['server'])->toBeString();
    expect($config['model'])->toBeString();
    expect($config['resources']['resource'])->toBeString();
});

