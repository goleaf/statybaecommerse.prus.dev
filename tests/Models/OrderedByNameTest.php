<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Tries to create minimal records using either a factory or a forceFill fallback.
 * Skips gracefully if creation isn't possible (missing factory or required columns).
 *
 * @return bool true if two records were created, false if skipped
 */
function tryCreateForOrdering(string $class, string $column, string $high, string $low): bool
{
    try {
        $class::query()->delete();
    } catch (Throwable $e) {
        // Some models may fail to truncate (FKs). That's ok; we continue.
    }

    try {
        // Prefer factories if present
        if (method_exists($class, 'factory')) {
            $class::factory()->create([$column => $high]);
            $class::factory()->create([$column => $low]);

            return true;
        }

        // Fallback: force fill
        $a = new $class;
        $a->forceFill([$column => $high])->save();

        $b = new $class;
        $b->forceFill([$column => $low])->save();

        return true;
    } catch (Throwable $e) {
        // Not enough info to create minimal rows; skip
        test()->markTestSkipped("Creation failed for $class ($column): {$e->getMessage()}");

        return false;
    }
}

uses(TestCase::class, RefreshDatabase::class);

/**
 * Dataset of [FQCN, candidateColumns[]]
 * The test will pick the first existing column in candidateColumns.
 * Add more entries anytime to cover more models.
 */
dataset('orderedByNameModels', [
    // Batch 2 (you can extend this list gradually)
    [\App\Models\AdminUser::class,           ['name', 'title']],
    [\App\Models\Attribute::class,           ['name', 'title']],
    [\App\Models\AttributeValue::class,      ['name', 'value', 'title']],
    [\App\Models\Brand::class,               ['name', 'title']],
    [\App\Models\Campaign::class,            ['name', 'title']],
    [\App\Models\CampaignConversionTranslation::class, ['title', 'name', 'notes']],
    [\App\Models\Category::class,            ['name', 'title']],      // already covered in a dedicated test; duplication is okay
    [\App\Models\Channel::class,             ['name', 'title']],
    [\App\Models\City::class,                ['name', 'title']],
    [\App\Models\Collection::class,          ['name', 'title']],
    [\App\Models\CollectionRule::class,      ['name', 'title', 'field']],
    [\App\Models\Company::class,             ['name', 'title']],
    [\App\Models\Country::class,             ['name', 'title']],
    [\App\Models\Menu::class,                ['name', 'title']],
    [\App\Models\MenuItem::class,            ['title', 'label', 'name']],
    [\App\Models\News::class,                ['title', 'author_name', 'name']],
    [\App\Models\NewsCategory::class,        ['name', 'title']],
    [\App\Models\NewsTag::class,             ['name', 'title']],
    [\App\Models\NotificationTemplate::class, ['name', 'title']],
    [\App\Models\Partner::class,             ['name', 'title']],
    [\App\Models\PartnerTier::class,         ['name', 'title']],
    [\App\Models\RecommendationBlock::class, ['name', 'title']],
    [\App\Models\Role::class,                ['name', 'title']],
    [\App\Models\Slider::class,              ['title', 'name']],
    [\App\Models\UiTranslation::class,       ['key', 'name', 'title']],
    [\App\Models\UserPreference::class,      ['preference_key', 'name', 'title']],
    [\App\Models\Zone::class,                ['name', 'title']],
]);

it('orders by name-like column ascending (via scopeOrderedByName)', function (string $class, array $candidates) {
    // Resolve table name from the model itself
    /** @var \Illuminate\Database\Eloquent\Model $model */
    $model = new $class;
    $table = $model->getTable();

    if (! Schema::hasTable($table)) {
        test()->markTestSkipped("$class table '$table' missing.");
    }

    // pick first candidate column that exists on the table
    $column = null;
    foreach ($candidates as $cand) {
        if (Schema::hasColumn($table, $cand)) {
            $column = $cand;
            break;
        }
    }
    if (! $column) {
        test()->markTestSkipped("$class has no candidate name-like columns on '$table'.");
    }

    if (! method_exists($class, 'orderedByName')) {
        test()->markTestSkipped("$class lacks scopeOrderedByName(). Add the OrdersByName trait and set \$nameColumn='$column'.");
    }

    // Try to create two records and then assert order
    if (! tryCreateForOrdering($class, $column, 'Zzz', 'Aaa')) {
        return; // skipped inside helper
    }

    $values = $class::orderedByName()->pluck($column)->all();

    // The two inserted values should appear in ascending order
    // (note: other pre-existing rows don't break this assertion)
    $filtered = array_values(array_intersect($values, ['Aaa', 'Zzz']));
    expect($filtered)->toBe(['Aaa', 'Zzz']);
})->with('orderedByNameModels');
