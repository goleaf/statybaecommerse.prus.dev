<?php declare(strict_types=1);

use App\Models\DiscountCode;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('has discount and redemptions relations', function (): void {
    $m = new DiscountCode();
    expect($m->discount())->toBeInstanceOf(BelongsTo::class);
    expect($m->redemptions())->toBeInstanceOf(HasMany::class);
});

it('validation helpers behave logically', function (): void {
    $m = new DiscountCode(['max_uses' => 1, 'usage_count' => 1]);
    expect($m->hasReachedLimit())->toBeTrue();

    $m = new DiscountCode(['max_uses' => 2, 'usage_count' => 1]);
    expect($m->hasReachedLimit())->toBeFalse();

    $m = new DiscountCode(['expires_at' => now()->subDay()]);
    expect($m->isValid())->toBeFalse();
});
