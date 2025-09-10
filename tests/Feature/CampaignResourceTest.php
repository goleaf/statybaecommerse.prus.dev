<?php declare(strict_types=1);

use App\Models\Campaign;
use App\Models\User;

it('loads campaigns index', function () {
    $admin = User::factory()->create(['email' => 'admin@admin.test']);
    actingAs($admin);

    $response = get('/admin/campaigns');
    $response->assertStatus(200);
});

it('loads campaigns edit page', function () {
    $admin = User::factory()->create(['email' => 'admin@admin.test']);
    actingAs($admin);

    $campaign = Campaign::factory()->create();

    $response = get("/admin/campaigns/{$campaign->id}/edit");
    $response->assertStatus(200);
});


