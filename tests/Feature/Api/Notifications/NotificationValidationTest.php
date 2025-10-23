<?php

declare(strict_types=1);

use App\Models\User;

it('rejects invalid pagination values', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/notifications?per_page=foo');

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['per_page'],
        ]);
});

it('requires a search query parameter', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/notifications/search');

    $response->assertStatus(422)
        ->assertJsonStructure([
            'message',
            'errors' => ['q'],
        ]);
});
