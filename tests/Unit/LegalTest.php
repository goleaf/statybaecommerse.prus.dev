<?php declare(strict_types=1);

use App\Models\Legal;

it('legal model is instantiable', function (): void {
    $m = new Legal();
    expect($m)->toBeInstanceOf(Legal::class);
});
