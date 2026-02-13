<?php

declare(strict_types=1);

use App\Services\Pricing\PriceConfiguration;

it('always resolves eur as pricing currency', function (): void {
    $usdConfigured = new PriceConfiguration([
        'currency' => 'USD',
    ]);

    $emptyConfigured = new PriceConfiguration([]);

    expect($usdConfigured->currency())->toBe('EUR');
    expect($emptyConfigured->currency())->toBe('EUR');
});
