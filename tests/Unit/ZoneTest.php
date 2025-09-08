<?php declare(strict_types=1);

use App\Models\Discount;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('has discounts relationship', function (): void {
    $zone = new Zone();
    $relation = $zone->discounts();

    expect($relation)
        ->toBeInstanceOf(HasMany::class)
        ->and($relation->getRelated()::class)
        ->toBe(Discount::class);
});

it('casts is_enabled to boolean', function (): void {
    $zone = new Zone([
        'is_enabled' => 1,
    ]);

    expect($zone->is_enabled)->toBeTrue();
});
