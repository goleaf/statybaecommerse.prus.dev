<?php

declare(strict_types=1);

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;

it('unit: product relation labels are translated', function (string $locale): void {
    $originalLocale = App::getLocale();

    App::setLocale($locale);

    $keys = [
        'messages.variants',
        'messages.images',
        'messages.features',
        'messages.requests',
        'messages.message',
        'messages.similarities',
        'messages.similar_product',
        'messages.feature_type',
        'messages.feature_key',
        'messages.feature_value',
        'messages.similarity_score',
        'messages.algorithm_type',
        'messages.requested_quantity',
        'messages.admin_notes',
        'messages.sort_order',
        'messages.path',
        'messages.calculated_at',
        'messages.unit_kg',
        'messages.unit_cm',
    ];

    foreach ($keys as $key) {
        expect(Lang::has($key, $locale, false))->toBeTrue();
    }

    App::setLocale($originalLocale);
})->with(['en', 'lt']);
