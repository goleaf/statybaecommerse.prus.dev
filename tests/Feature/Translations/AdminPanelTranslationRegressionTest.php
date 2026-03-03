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
        'admin.suppliers.model_label',
        'admin.suppliers.navigation_label',
        'admin.suppliers.publish_on_create_not_allowed',
        'admin.suppliers.publish_requires_supplier',
        'admin.brochures.model_label',
        'admin.brochures.navigation_label',
        'admin.brochures.requires_active_file',
    ];

    foreach ($requiredKeys as $key) {
        expect(Lang::hasForLocale($key, $locale))->toBeTrue("Missing [{$key}] for locale [{$locale}]");
        expect(trans($key, [], $locale))->not->toBe($key);
    }

    expect(Lang::hasForLocale('messages.Enabled', $locale))->toBeFalse();
    expect(trans('messages.Enabled', [], $locale))->toBe('messages.Enabled');
})->with(['en', 'lt', 'de', 'ru']);
