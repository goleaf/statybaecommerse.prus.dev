<?php

declare(strict_types=1);

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('defines the expected fillable attributes', function (): void {
    // Instantiate a fresh model instance to inspect its mass assignment configuration.
    $fillable = (new AuditLog())->getFillable();

    // Ensure each attribute that should be mass assignable is explicitly listed.
    expect($fillable)->toBe([
        'entity_type',
        'entity_id',
        'action',
        'user_id',
        'diff',
    ]);
});

it('casts the diff attribute to an array when retrieved from the database', function (): void {
    // Persist a log entry using array data so the cast behaviour can be asserted.
    $log = AuditLog::query()->create([
        'entity_type' => User::class,
        'entity_id'   => 'example-id',
        'action'      => 'created',
        'user_id'     => null,
        'diff'        => ['field' => 'value'],
    ])->fresh();

    // The cast should guarantee a PHP array regardless of the storage backend representation.
    expect($log->diff)->toBe(['field' => 'value']);
});

it('exposes the morph relation for the audited entity', function (): void {
    // Calling the relation method should yield a MorphTo relation for polymorphic lookups.
    $relation = (new AuditLog())->entity();

    // Validate that the relationship contract matches the expected polymorphic type.
    expect($relation)->toBeInstanceOf(MorphTo::class);
});

it('exposes the belongs-to relation for the user who triggered the action', function (): void {
    // Fetch the relation definition without needing to hit the database.
    $relation = (new AuditLog())->user();

    // Confirm the relation allows traversing back to the owning user model.
    expect($relation)->toBeInstanceOf(BelongsTo::class);
});

it('scopes results to a specific entity instance', function (): void {
    // Create a pair of users so we can persist logs for different entities.
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // Store audit logs for each user so the scope can filter by morph type and identifier.
    $matching = AuditLog::query()->create([
        'entity_type' => $user->getMorphClass(),
        'entity_id'   => (string) $user->getKey(),
        'action'      => 'updated',
        'user_id'     => null,
        'diff'        => ['name' => ['old' => 'A', 'new' => 'B']],
    ]);

    AuditLog::query()->create([
        'entity_type' => $otherUser->getMorphClass(),
        'entity_id'   => (string) $otherUser->getKey(),
        'action'      => 'updated',
        'user_id'     => null,
        'diff'        => null,
    ]);

    // Execute the scope using the model instance to ensure both type and identifier constraints are honoured.
    $results = AuditLog::query()->forEntity($user)->get();

    // Only the matching log should be returned when filtering by the concrete model.
    expect($results)->toHaveCount(1)
        ->and($results->sole()->is($matching))->toBeTrue();
});

it('scopes results when provided with an entity alias and identifier', function (): void {
    // Insert logs that share the same morph alias but target different identifiers.
    AuditLog::query()->create([
        'entity_type' => User::class,
        'entity_id'   => 'alpha',
        'action'      => 'deleted',
        'user_id'     => null,
        'diff'        => null,
    ]);

    AuditLog::query()->create([
        'entity_type' => User::class,
        'entity_id'   => 'beta',
        'action'      => 'deleted',
        'user_id'     => null,
        'diff'        => null,
    ]);

    // Apply the scope using the alias and explicit identifier to narrow down the results.
    $results = AuditLog::query()->forEntity(User::class, 'alpha')->get();

    // The query should isolate only the record that matches the provided identifier.
    expect($results)->toHaveCount(1)
        ->and($results->sole()->entity_id)->toBe('alpha');
});

it('scopes results by action keyword', function (): void {
    // Seed different audit events to confirm the scope filters by the action column.
    AuditLog::query()->create([
        'entity_type' => User::class,
        'entity_id'   => '42',
        'action'      => 'restored',
        'user_id'     => null,
        'diff'        => null,
    ]);

    AuditLog::query()->create([
        'entity_type' => User::class,
        'entity_id'   => '42',
        'action'      => 'created',
        'user_id'     => null,
        'diff'        => null,
    ]);

    // Request only the "restored" action to verify the scope applies the filter correctly.
    $results = AuditLog::query()->forAction('restored')->get();

    // Exactly one record should satisfy the action constraint.
    expect($results)->toHaveCount(1)
        ->and($results->sole()->action)->toBe('restored');
});

it('scopes results by user identifier including null values', function (): void {
    // Capture both anonymous and user-attributed audit logs for comparison.
    $user = User::factory()->create();

    AuditLog::query()->create([
        'entity_type' => User::class,
        'entity_id'   => (string) $user->getKey(),
        'action'      => 'created',
        'user_id'     => $user->getKey(),
        'diff'        => null,
    ]);

    AuditLog::query()->create([
        'entity_type' => User::class,
        'entity_id'   => (string) $user->getKey(),
        'action'      => 'created',
        'user_id'     => null,
        'diff'        => null,
    ]);

    // Filter specifically for anonymous audit logs to ensure nullable IDs are treated distinctly.
    $anonymousResults = AuditLog::query()->forUser(null)->get();

    // Only the anonymous record should be returned when the user ID is null.
    expect($anonymousResults)->toHaveCount(1)
        ->and($anonymousResults->sole()->user_id)->toBeNull();
});
