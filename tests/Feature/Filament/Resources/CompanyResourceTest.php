<?php

declare(strict_types=1);

use App\Filament\Resources\CompanyResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('mounts the CompanyResource index page', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $this
        ->get(CompanyResource::getUrl('index'))
        ->assertOk();
});
