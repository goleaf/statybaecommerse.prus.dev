<?php declare(strict_types=1);

use App\Models\Channel;

it('instantiates Channel model', function (): void {
    expect(new Channel())->toBeInstanceOf(Channel::class);
});
