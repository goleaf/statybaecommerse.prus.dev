<?php

declare(strict_types=1);

use App\Application\DTOs\Notifications\NotificationSearchData;

test('search query must be provided', function (): void {
    expect(fn () => new NotificationSearchData(''))->toThrow(InvalidArgumentException::class);
    expect(fn () => new NotificationSearchData('   '))->toThrow(InvalidArgumentException::class);
});

