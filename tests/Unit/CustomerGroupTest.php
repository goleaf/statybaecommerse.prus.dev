<?php declare(strict_types=1);

use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

it('has users belongsToMany relation', function (): void {
    $m = new CustomerGroup();
    expect($m->users())->toBeInstanceOf(BelongsToMany::class);
});
