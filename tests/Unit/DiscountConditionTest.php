<?php declare(strict_types=1);

use App\Models\DiscountCondition;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

it('belongs to discount', function (): void {
    $m = new DiscountCondition();
    expect($m->discount())->toBeInstanceOf(BelongsTo::class);
});

it('matches operator logic', function (): void {
    $m = new DiscountCondition(['operator' => 'equals_to', 'value' => 'abc']);
    expect($m->matches('abc'))->toBeTrue();
    $m = new DiscountCondition(['operator' => 'not_equals_to', 'value' => 'abc']);
    expect($m->matches('xyz'))->toBeTrue();
    $m = new DiscountCondition(['operator' => 'starts_with', 'value' => 'ab']);
    expect($m->matches('abc'))->toBeTrue();
    $m = new DiscountCondition(['operator' => 'ends_with', 'value' => 'bc']);
    expect($m->matches('abc'))->toBeTrue();
    $m = new DiscountCondition(['operator' => 'contains', 'value' => 'b']);
    expect($m->matches('abc'))->toBeTrue();
    $m = new DiscountCondition(['operator' => 'not_contains', 'value' => 'z']);
    expect($m->matches('abc'))->toBeTrue();
});
