<?php

declare(strict_types=1);

use App\Models\Product;

it('determines published state correctly', function () {
    $p = new Product;
    $p->is_visible = true;
    $p->published_at = null;
    expect($p->isPublished())->toBeFalse();

    $p->published_at = now()->addDay();
    expect($p->isPublished())->toBeFalse();

    $p->published_at = now()->subMinute();
    expect($p->isPublished())->toBeTrue();

    $p->is_visible = false;
    expect($p->isPublished())->toBeFalse();
});
