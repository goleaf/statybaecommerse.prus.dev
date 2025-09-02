<?php declare(strict_types=1);

use App\Models\Collection;

it('flushCaches runs without error', function (): void {
    config()->set('app.supported_locales', 'en,fr');
    Collection::flushCaches();
    expect(true)->toBeTrue();
});
