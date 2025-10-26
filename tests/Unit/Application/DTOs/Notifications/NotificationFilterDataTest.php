<?php

declare(strict_types=1);

use App\Application\DTOs\Notifications\NotificationFilterData;

test('per page must be positive and within bounds', function (): void {
    expect(fn () => new NotificationFilterData(0))->toThrow(InvalidArgumentException::class);
    expect(fn () => new NotificationFilterData(101))->toThrow(InvalidArgumentException::class);
});

test('type cannot be empty string', function (): void {
    expect(fn () => new NotificationFilterData(type: '   '))->toThrow(InvalidArgumentException::class);
});
