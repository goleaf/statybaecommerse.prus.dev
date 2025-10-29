<?php

declare(strict_types=1);

use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Tests\Support\AssertsRelations;
use Tests\TestCase;

// Boot the full Laravel test harness so Facade calls such as Schema::class resolve correctly.
uses(TestCase::class);

// Provide a compact dataset describing how the OrdersByName concern should behave for user preferences.
dataset('ordered_by_name_user_preference', [
    [UserPreference::class, ['name', 'key']],
]);

it('registers user preference in the ordered-by-name dataset', function (string $class, array $aliases): void {
    // Guard against missing schema pieces because some CI suites boot from slim SQLite snapshots.
    /** @var UserPreference $model Ensures static analysers understand the instantiated model type for facade interactions. */
    $model = new $class;

    /** @var string $table Explicitly capture the Eloquent table name so Schema facade checks stay type-safe. */
    $table = $model->getTable();

    if (! Schema::hasTable($table)) {
        markTestSkipped("{$table} table missing for ordered-by-name coverage checks.");
    }

    /** @var list<string> $aliases */
    foreach ($aliases as $alias) {
        /** @var string $alias */
        // Map the alias to its persisted column so ordering assertions remain truthful.
        $column = match ($alias) {
            'name'  => 'preference_type',
            'key'   => 'preference_key',
            default => $alias,
        };

        if (! Schema::hasColumn($table, $column)) {
            markTestSkipped("{$column} column missing for ordered-by-name coverage checks.");
        }
    }

    // Confirm the shared scope appends the expected ORDER BY clause via the configured alias mapping.
    /** @var string $sql Capture the generated SQL so we can normalise quoting across database drivers. */
    $sql = $model->newQuery()->orderedByName()->toSql();

    // Strip driver-specific quoting so SQLite (double quotes) and MySQL (backticks) both satisfy the assertion.
    $normalisedSql = strtolower((string) preg_replace('/[`"\[\]]/', '', $sql));

    expect($normalisedSql)->toContain('order by user_preferences.preference_key asc');
})->with('ordered_by_name_user_preference');

it('user preference links back to the owning user', function (): void {
    // Exercise the relation helper to guarantee BelongsTo< User > remains intact after refactors.
    $model = new UserPreference;

    AssertsRelations::assertRelation($model, 'user', BelongsTo::class);
});
