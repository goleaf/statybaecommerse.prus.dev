<?php

declare(strict_types=1);

use App\Filament\Pages\Reports;
use App\Models\User;

it('registers reports dashboard page route and mounts', function (): void {
    $adminUser = User::factory()->create(['email' => 'admin@example.com']);

    actingAs($adminUser)
        ->get(Reports::getUrl())
        ->assertStatus(200);
});
