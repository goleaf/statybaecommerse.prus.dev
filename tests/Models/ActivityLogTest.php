<?php

declare(strict_types=1);

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('activity log factory creates records', function (): void {
    // Arrange & Act: persist a freshly generated activity log for the assertion.
    ActivityLog::factory()->create();

    // Assert: ensure at least one row exists in the table.
    expect(ActivityLog::count())->toBe(1);
});

test('orderedByName scope sorts by log name ascending', function (): void {
    // Arrange: create two logs in reverse alphabetical order.
    $user = User::factory()->create();
    ActivityLog::factory()->for($user, 'user')->create(['log_name' => 'Zulu']);
    ActivityLog::factory()->for($user, 'user')->create(['log_name' => 'Alpha']);

    // Act: fetch ordered results using the dedicated scope.
    $ordered = ActivityLog::orderedByName()->pluck('log_name')->all();

    // Assert: the names should be returned alphabetically.
    expect($ordered)->toBe(['Alpha', 'Zulu']);
});

test('relations expose expected relation objects', function (): void {
    // Arrange: obtain a model instance without touching the database.
    $model = new ActivityLog;

    // Assert: ensure relationship methods return the proper relation types.
    expect($model->user())->toBeInstanceOf(BelongsTo::class);
    expect($model->subject())->toBeInstanceOf(MorphTo::class);
    expect($model->causer())->toBeInstanceOf(MorphTo::class);
});
