<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Lang;

it('provides critical admin translation keys and does not expose deprecated messages.Enabled key', function (string $locale): void {
    $requiredKeys = [
        'messages.enabled',
        'messages.movements',
        'messages.visible',
        'messages.comments',
        'messages.shipping_method',
        'attribute.pack_size',
        'admin.navigation.coupons',
        'admin.inventory.stock_reservations',
        'admin.products.similar_products',
    ];

    foreach ($requiredKeys as $key) {
        expect(Lang::hasForLocale($key, $locale))->toBeTrue("Missing [{$key}] for locale [{$locale}]");
        expect(trans($key, [], $locale))->not->toBe($key);
    }

    expect(Lang::hasForLocale('messages.Enabled', $locale))->toBeFalse();
    expect(trans('messages.Enabled', [], $locale))->toBe('messages.Enabled');
})->with(['en', 'lt', 'de', 'ru']);
