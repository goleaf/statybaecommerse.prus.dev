<?php declare(strict_types=1);

use App\Models\Category;

it('flushCaches runs without error', function (): void {
    config()->set('app.supported_locales', 'en,fr');
    Category::flushCaches();
    expect(true)->toBeTrue();
});
