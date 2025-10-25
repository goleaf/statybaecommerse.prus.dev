<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

final class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes_are_defined(): void
    {
        // Arrange: instantiate a new model instance to introspect its fillable definition.
        $model = new ActivityLog();

        // Assert: ensure that key attributes are mass assignable for factory support.
        $this->assertEqualsCanonicalizing([
            'log_name',
            'description',
            'event',
            'subject_type',
            'subject_id',
            'causer_type',
            'causer_id',
            'properties',
            'batch_uuid',
            'ip_address',
            'user_agent',
            'device_type',
            'browser',
            'os',
            'country',
            'is_important',
            'is_system',
            'severity',
            'category',
            'notes',
        ], $model->getFillable());
    }

    public function test_casts_are_configured(): void
    {
        // Arrange: create the model instance to pull its casts configuration.
        $model = new ActivityLog();

        // Assert: confirm the expected casts are present to avoid runtime surprises.
        foreach ([
            'properties' => 'array',
            'is_important' => 'boolean',
            'is_system' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ] as $attribute => $cast) {
            // Each expected cast should exist with the proper configuration value.
            $this->assertArrayHasKey($attribute, $model->getCasts());
            $this->assertSame($cast, $model->getCasts()[$attribute]);
        }
    }

    public function test_user_relationship_returns_belongs_to(): void
    {
        // Arrange: build a user and a related activity log record.
        $user = User::factory()->create();
        $log = ActivityLog::factory()->create([
            'causer_id' => $user->id,
        ]);

        // Assert: verify the relationship type and the resolved model instance.
        $this->assertInstanceOf(BelongsTo::class, $log->user());
        $this->assertTrue($log->user->is($user));
    }

    public function test_scope_ordered_by_name_sorts_records(): void
    {
        // Arrange: seed out-of-order activity logs so the scope can be exercised.
        ActivityLog::factory()->create(['log_name' => 'system']);
        ActivityLog::factory()->create(['log_name' => 'auth']);
        ActivityLog::factory()->create(['log_name' => 'billing']);

        // Act: collect the ordered names using the new scope.
        $orderedNames = ActivityLog::query()
            ->orderedByName()
            ->pluck('log_name');

        // Assert: ensure the names are sorted lexicographically as intended.
        $this->assertInstanceOf(Collection::class, $orderedNames);
        $this->assertSame(['auth', 'billing', 'system'], $orderedNames->all());
    }
}
