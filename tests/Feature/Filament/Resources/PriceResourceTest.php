<?php

declare(strict_types=1);

use App\Filament\Resources\PriceResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('feature: mounts the PriceResource index page', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $this
        ->get(PriceResource::getUrl('index'))
        ->assertOk();
});
