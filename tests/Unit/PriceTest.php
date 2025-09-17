<?php declare(strict_types=1);

use App\Models\Price;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

it('belongs to currency', function (): void {
    $m = new Price();
    expect($m->currency())->toBeInstanceOf(BelongsTo::class);
});
