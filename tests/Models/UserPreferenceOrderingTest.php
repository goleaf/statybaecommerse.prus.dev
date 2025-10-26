<?php declare(strict_types=1);

use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Tests\Support\AssertsRelations;

// Provide a compact dataset describing how the OrdersByName concern should behave for user preferences.
dataset('ordered_by_name_user_preference', [
    [UserPreference::class, ['name', 'key']],
]);

it('registers user preference in the ordered-by-name dataset', function (string $class, array $aliases): void {
    // Guard against missing schema pieces because some CI suites boot from slim SQLite snapshots.
    $model = new $class;
    $table = $model->getTable();

    if (! Schema::hasTable($table)) {
        markTestSkipped("{$table} table missing for ordered-by-name coverage checks.");
    }

    foreach ($aliases as $alias) {
        // Map the alias to its persisted column so ordering assertions remain truthful.
        $column = $alias === 'name' ? 'preference_type' : ($alias === 'key' ? 'preference_key' : $alias);

        if (! Schema::hasColumn($table, $column)) {
            markTestSkipped("{$column} column missing for ordered-by-name coverage checks.");
        }
    }

    // Confirm the shared scope appends the expected ORDER BY clause via the configured alias mapping.
    expect($model->newQuery()->orderedByName()->toSql())->toContain('order by `preference_key` asc');
})->with('ordered_by_name_user_preference');

it('user preference links back to the owning user', function (): void {
    // Exercise the relation helper to guarantee BelongsTo< User > remains intact after refactors.
    $model = new UserPreference;

    AssertsRelations::assertRelation($model, 'user', BelongsTo::class);
});
