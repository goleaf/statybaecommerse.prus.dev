<?php

declare(strict_types=1);

use App\Filament\Resources\PriceListResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('mounts the PriceListResource index page', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $this
        ->get(PriceListResource::getUrl('index'))
        ->assertOk();
});
