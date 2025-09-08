<?php declare(strict_types=1);

use App\Models\Discount;
use App\Models\DiscountCode;
use App\Models\DiscountCondition;
use App\Models\DiscountRedemption;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('discount relations are defined', function () {
    $d = new Discount();

    expect($d->conditions())
        ->toBeInstanceOf(HasMany::class)
        ->and($d->codes())
        ->toBeInstanceOf(HasMany::class)
        ->and($d->redemptions())
        ->toBeInstanceOf(HasMany::class)
        ->and($d->zone())
        ->toBeInstanceOf(BelongsTo::class);
});

it('discount validity respects status and dates', function () {
    $d = new Discount(['status' => 'active']);

    expect($d->isValid())->toBeTrue();

    $d->starts_at = now()->addDay();
    expect($d->isValid())->toBeFalse();

    $d->starts_at = now()->subDay();
    $d->ends_at = now()->subMinute();
    expect($d->isValid())->toBeFalse();

    $d->ends_at = now()->addDay();
    expect($d->isValid())->toBeTrue();
});

it('discount code validity and limits work', function () {
    $c = new DiscountCode(['max_uses' => 1, 'usage_count' => 0]);
    expect($c->hasReachedLimit())->toBeFalse()->and($c->isValid())->toBeTrue();

    $c->usage_count = 1;
    expect($c->hasReachedLimit())->toBeTrue()->and($c->isValid())->toBeFalse();

    $c->max_uses = null;
    $c->usage_count = 5;
    $c->expires_at = now()->subMinute();
    expect($c->isValid())->toBeFalse();
});

it('discount condition operators behave correctly', function () {
    $cond = new DiscountCondition(['operator' => 'equals_to', 'value' => 'abc']);
    expect($cond->matches('abc'))->toBeTrue();

    $cond->operator = 'not_equals_to';
    expect($cond->matches('abc'))->toBeFalse();

    $cond->operator = 'contains';
    $cond->value = 'b';
    expect($cond->matches('abc'))->toBeTrue();

    $cond->operator = 'starts_with';
    $cond->value = 'a';
    expect($cond->matches('abc'))->toBeTrue();

    $cond->operator = 'ends_with';
    $cond->value = 'c';
    expect($cond->matches('abc'))->toBeTrue();
});

it('discount redemption scopes exist', function () {
    $r = new DiscountRedemption();
    expect(method_exists($r, 'scopeForDiscount'))
        ->toBeTrue()
        ->and(method_exists($r, 'scopeForUser'))
        ->toBeTrue()
        ->and(method_exists($r, 'scopeForOrder'))
        ->toBeTrue();
});
