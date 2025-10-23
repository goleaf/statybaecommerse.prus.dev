<?php

declare(strict_types=1);

use App\Support\Filament\SearchableInputHelper;

test('clear helper flushes dependent keys', function (): void {
    $calls = [];

    $set = function (string $field, mixed $value) use (&$calls): void {
        $calls[$field] = $value;
    };

    SearchableInputHelper::clear($set, [
        'product_id' => null,
        'name'       => 'Example',
    ]);

    expect($calls)->toBe([
        'product_id' => null,
        'name'       => 'Example',
    ]);
});
