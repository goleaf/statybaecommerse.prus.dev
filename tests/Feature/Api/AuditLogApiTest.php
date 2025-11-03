<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class AuditLogApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('authorization.testing.skip_checks', false);

        // Seed permissions and roles for all guards
        $this->seed(\Database\Seeders\AdminAuthorizationSeeder::class);
    }

    public function test_it_returns_paginated_audit_logs_for_entity(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Sanctum::actingAs($user, ['*']); // Provide an authenticated actor so audit attribution is deterministic.

        $document = Document::factory()->create([
            'status'     => 'draft',
            'title'      => 'Original title',
            'created_by' => $user->getKey(),
            'updated_by' => $user->getKey(),
        ]);

        $document->update(['status' => 'published']);

        $response = $this->getJson(route('api.audit-logs.index', [
            'entity_type' => $document->getMorphClass(),
            'entity_id'   => (string) $document->getKey(),
        ]));

        $response->assertOk()
            ->assertJsonPath('data.0.entity_type', $document->getMorphClass())
            ->assertJsonPath('data.0.entity_id', (string) $document->getKey())
            ->assertJsonPath('data.0.action', 'updated')
            ->assertJsonPath('data.0.user.id', $user->getKey());
    }

    public function test_filters_by_action_when_requested(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        Sanctum::actingAs($user, ['*']); // Ensure the created log is tagged with a single user for filtering assertions.

        $document = Document::factory()->create([
            'status'     => 'draft',
            'created_by' => $user->getKey(),
            'updated_by' => $user->getKey(),
        ]);

        $document->update(['status' => 'archived']);

        $response = $this->getJson(route('api.audit-logs.index', [
            'entity_type' => $document->getMorphClass(),
            'entity_id'   => (string) $document->getKey(),
            'action'      => 'created',
        ]));

        $response->assertOk();
        $this->assertSame('created', $response->json('data.0.action'));
        $this->assertCount(1, $response->json('data'));
    }

    public function test_viewer_role_is_forbidden_from_audit_logs(): void
    {
        $user = User::factory()->create();
        $user->assignRole('viewer');
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson(route('api.audit-logs.index'));

        $response->assertForbidden();
    }
}
