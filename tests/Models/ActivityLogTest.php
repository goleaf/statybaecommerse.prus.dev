<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

final class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_log_factory_creates_records(): void
    {
        ActivityLog::factory()->create();

        $this->assertSame(1, ActivityLog::count());
    }

    public function test_ordered_by_name_scope_sorts_by_log_name_ascending(): void
    {
        $user = User::factory()->create();
        ActivityLog::factory()->for($user, 'user')->create(['log_name' => 'Zulu']);
        ActivityLog::factory()->for($user, 'user')->create(['log_name' => 'Alpha']);

        $ordered = ActivityLog::orderedByName()->pluck('log_name');

        $this->assertInstanceOf(Collection::class, $ordered);
        $this->assertSame(['Alpha', 'Zulu'], $ordered->all());
    }

    public function test_relations_expose_expected_relation_objects(): void
    {
        $model = new ActivityLog();

        $this->assertInstanceOf(BelongsTo::class, $model->user());
        $this->assertInstanceOf(MorphTo::class, $model->subject());
        $this->assertInstanceOf(MorphTo::class, $model->causer());
    }

    public function test_fillable_attributes_are_defined(): void
    {
        $model = new ActivityLog();

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
        $model = new ActivityLog();

        foreach ([
            'properties' => 'array',
            'is_important' => 'boolean',
            'is_system' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ] as $attribute => $cast) {
            $this->assertArrayHasKey($attribute, $model->getCasts());
            $this->assertSame($cast, $model->getCasts()[$attribute]);
        }
    }

    public function test_user_relationship_returns_belongs_to(): void
    {
        $user = User::factory()->create();
        $log = ActivityLog::factory()->create([
            'causer_id' => $user->id,
        ]);

        $this->assertInstanceOf(BelongsTo::class, $log->user());
        $this->assertTrue($log->user->is($user));
    }
}

