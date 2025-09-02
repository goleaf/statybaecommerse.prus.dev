<?php declare(strict_types=1);

use App\Models\Discount;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('casts and fillable are configured', function (): void {
    $d = new Discount();
    expect($d->getCasts())
        ->toHaveKey('value')
        ->toHaveKey('min_spend')
        ->toHaveKey('starts_at')
        ->toHaveKey('ends_at');
});

it('has relations: conditions, codes, redemptions, zone', function (): void {
    $d = new Discount();
    expect($d->conditions())->toBeInstanceOf(HasMany::class);
    expect($d->codes())->toBeInstanceOf(HasMany::class);
    expect($d->redemptions())->toBeInstanceOf(HasMany::class);
    expect($d->zone())->toBeInstanceOf(BelongsTo::class);
});

it('scopes behave syntactically', function (): void {
    $q = Discount::query();
    expect($q->active())->toBeObject();
    expect($q->scheduled())->toBeObject();
    expect($q->expired())->toBeObject();
});
