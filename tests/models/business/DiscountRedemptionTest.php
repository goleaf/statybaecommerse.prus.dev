<?php

declare(strict_types=1);

use App\Models\DiscountRedemption;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

it('has relationships to discount, code, and user', function (): void {
    $m = new DiscountRedemption;
    expect($m->discount())->toBeInstanceOf(BelongsTo::class);
    expect($m->code())->toBeInstanceOf(BelongsTo::class);
    expect($m->user())->toBeInstanceOf(BelongsTo::class);
});
