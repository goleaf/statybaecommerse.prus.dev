<?php

declare(strict_types=1);

it('unit: telescope watchers are configured using string class names', function (): void {
    $watchers = config('telescope.watchers');

    expect($watchers)->toBeArray();
    foreach (array_keys($watchers) as $key) {
        expect($key)->toBeString()->toStartWith('Laravel\\Telescope\\Watchers\\');
    }
});

