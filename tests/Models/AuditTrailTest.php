<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\AdminUser;
use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JsonException;
use Tests\TestCase;

final class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_trail_has_expected_fillable_and_casts(): void
    {
        // Instantiate the model to inspect its configuration without persisting records.
        $model = new AuditTrail;

        // Verify that the fillable attributes guard against mass-assignment vulnerabilities.
        self::assertSame([
            'auditable_type',
            'auditable_id',
            'event',
            'actor_type',
            'actor_id',
            'reason',
            'request_id',
            'diff',
        ], $model->getFillable());

        // Ensure the diff payload is automatically converted to an array when retrieved.
        self::assertArrayHasKey('diff', $model->getCasts());
        self::assertSame('array', $model->getCasts()['diff']);
    }

    public function test_auditable_and_actor_relationships_resolve_models(): void
    {
        // Create related models to link to the audit entry.
        $auditable = User::factory()->create();
        $actor = AdminUser::factory()->create();

        // Persist an audit trail entry referencing the created models.
        $auditTrail = AuditTrail::query()->create([
            'auditable_type' => $auditable->getMorphClass(),
            'auditable_id'   => $auditable->getKey(),
            'event'          => 'updated',
            'actor_type'     => $actor->getMorphClass(),
            'actor_id'       => $actor->getKey(),
            'reason'         => 'Testing relationships',
            'request_id'     => 'req-123',
            'diff'           => ['name' => ['previous' => 'Old', 'current' => 'New']],
        ])->fresh();

        // Confirm the morph relationships hydrate the expected model instances.
        self::assertInstanceOf(User::class, $auditTrail->auditable);
        self::assertTrue($auditable->is($auditTrail->auditable));

        self::assertInstanceOf(AdminUser::class, $auditTrail->actor);
        self::assertTrue($actor->is($auditTrail->actor));
    }

    /**
     * @throws JsonException
     */
    public function test_accessors_format_labels_and_diff_payload(): void
    {
        // Prepare a diff payload containing multiple fields for accessor testing.
        $diff = [
            'name'  => ['previous' => 'Before', 'current' => 'After'],
            'email' => ['previous' => 'before@example.test', 'current' => 'after@example.test'],
        ];

        // Create an audit record to exercise the attribute accessors.
        $auditTrail = AuditTrail::query()->create([
            'auditable_type' => User::class,
            'auditable_id'   => 42,
            'event'          => 'updated',
            'actor_type'     => null,
            'actor_id'       => null,
            'reason'         => null,
            'request_id'     => 'req-456',
            'diff'           => $diff,
        ])->fresh();

        // The auditable label should include the class basename and identifier.
        self::assertSame('User #42', $auditTrail->auditable_label);

        // Without an actor the accessor should fall back to the localized system label.
        self::assertSame(__('admin.audit_trails.system_actor'), $auditTrail->actor_display_name);

        // The diff keys accessor should render a comma-delimited list of changed fields.
        self::assertSame('name, email', $auditTrail->diff_keys);

        // Pretty printed JSON should be round-trip decodable while preserving the payload.
        self::assertSame($diff, json_decode($auditTrail->diff_pretty, true, 512, JSON_THROW_ON_ERROR));
    }

    public function test_diff_helper_detects_changes(): void
    {
        // Feed the diff helper with before/after data containing mixed value types.
        $diff = AuditTrail::diff(
            ['name' => 'Before', 'count' => 1, 'meta' => ['a' => 1]],
            ['name' => 'After', 'count' => 1, 'meta' => ['a' => 2]]
        );

        // The diff should only include keys whose values changed.
        self::assertSame([
            'name' => ['previous' => 'Before', 'current' => 'After'],
            'meta' => ['previous' => ['a' => 1], 'current' => ['a' => 2]],
        ], $diff);
    }

    public function test_values_differ_handles_common_types(): void
    {
        // Scalar comparison should respect equality rules.
        self::assertFalse(AuditTrail::valuesDiffer('same', 'same'));
        self::assertTrue(AuditTrail::valuesDiffer('before', 'after'));

        // Array comparisons should detect changes in nested structures.
        self::assertTrue(AuditTrail::valuesDiffer(['a' => 1], ['a' => 2]));

        // Identical numeric representations should be treated as equal.
        self::assertFalse(AuditTrail::valuesDiffer(5, 5.0));
    }

    /**
     * @throws JsonException
     */
    public function test_record_persists_entry_with_authenticated_actor(): void
    {
        // Create the actor and auditable models and authenticate the admin guard.
        $actor = AdminUser::factory()->create();
        $auditable = User::factory()->create();
        $this->actingAs($actor, 'admin');

        // Build a diff payload and persist it using the static helper.
        $diff = AuditTrail::diff(['name' => 'Before'], ['name' => 'After']);
        AuditTrail::record($auditable, $diff, 'updated', 'Reason for change');

        // Ensure the audit entry exists with the expected actor correlation data.
        $record = AuditTrail::query()->first();
        self::assertNotNull($record);
        self::assertSame($auditable->getMorphClass(), $record->auditable_type);
        self::assertSame($auditable->getKey(), $record->auditable_id);
        self::assertSame($actor->getMorphClass(), $record->actor_type);
        self::assertSame($actor->getKey(), $record->actor_id);
        self::assertSame('Reason for change', $record->reason);
        self::assertSame('updated', $record->event);
        self::assertNotEmpty($record->request_id);
        self::assertSame($diff, $record->diff);
    }

    public function test_record_ignores_empty_diffs(): void
    {
        // Create an auditable model and attempt to record with an empty diff payload.
        $auditable = User::factory()->create();
        AuditTrail::record($auditable, [], 'updated');

        // No entries should be stored when there are no changes to persist.
        self::assertSame(0, AuditTrail::query()->count());
    }
}
