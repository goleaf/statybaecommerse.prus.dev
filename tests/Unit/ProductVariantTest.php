<?php declare(strict_types=1);

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Relations\MorphMany;

it('has prices morphMany relation and helpers', function (): void {
    $m = new ProductVariant();
    expect($m->prices())->toBeInstanceOf(MorphMany::class);
    expect($m->isOutOfStock())->toBeBool();
});
