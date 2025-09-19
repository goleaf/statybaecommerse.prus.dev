<?php declare(strict_types=1);

use App\Filament\Resources\NewsResource;
use App\Models\News;
use App\Models\User;

it('can access news resource', function () {
    $user = User::factory()->create();

    $this
        ->actingAs($user)
        ->get(NewsResource::getUrl('index'))
        ->assertStatus(200);
});

