<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DocumentAuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_creation_persists_audit_log_with_actor(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user); // Authenticated context ensures attribution resolves to this actor.

        $document = Document::factory()->create([
            'status'     => 'draft',
            'format'     => 'pdf',
            'created_by' => null,
            'updated_by' => null,
        ]);

        $log = $document->auditLogs()->where('action', 'created')->first();

        $this->assertNotNull($log, 'Creation should produce an audit log entry.');
        $this->assertSame('created', $log->action);
        $this->assertSame($user->getKey(), $log->user_id, 'Actor ID should be captured on create.');
        $this->assertArrayHasKey('status', $log->diff['after'] ?? []);
    }

    public function test_document_update_records_before_and_after_changes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $document = Document::factory()->create([
            'status'     => 'draft',
            'title'      => 'Initial title',
            'created_by' => $user->getKey(),
            'updated_by' => $user->getKey(),
        ]);

        $document->update([
            'status' => 'published',
            'title'  => 'Updated title',
        ]);

        $log = $document->auditLogs()
            ->where('action', 'updated')
            ->latest('created_at')
            ->first();

        $this->assertNotNull($log, 'Update should create a diff entry.');
        // Before/after payloads should highlight the precise field transitions we applied above.
        $this->assertSame('draft', $log->diff['before']['status'] ?? null);
        $this->assertSame('published', $log->diff['after']['status'] ?? null);
        $this->assertSame('Updated title', $log->diff['after']['title'] ?? null);
    }
}
