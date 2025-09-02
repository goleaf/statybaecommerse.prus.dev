<?php declare(strict_types=1);

use App\Models\Channel;

it('channel model is instantiable', function (): void {
    $m = new Channel();
    expect($m)->toBeInstanceOf(Channel::class);
});
