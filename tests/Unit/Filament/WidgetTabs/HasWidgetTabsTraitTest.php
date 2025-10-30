<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\WidgetTabs;

use App\Filament\WidgetTabs\Components\WidgetTab;
use App\Filament\WidgetTabs\Concerns\HasWidgetTabs;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Tests\TestCase;

final class HasWidgetTabsTraitTest extends TestCase
{
    protected function tearDown(): void
    {
        // Close Mockery after each test to prevent cross-test expectations leaking.
        Mockery::close();

        parent::tearDown();
    }

    public function test_mount_initialises_default_tab_and_alias_properties(): void
    {
        // Arrange: instantiate the harness with a mocked builder so we avoid hitting the database.
        $builder = Mockery::mock(Builder::class);
        $builder->shouldIgnoreMissing();
        $component = new WidgetTabTraitHarness($builder);

        // Act: run the trait's mount logic to load the default widget tab.
        $component->mount();

        // Assert: both the canonical and legacy properties are aligned to the first tab.
        expect($component->activeWidgetTab)->toBe('overview')
            ->and($component->activeTab)->toBe('overview')
            ->and(array_keys($component->exposedGetCachedWidgetTabs()))
            ->toBe(['overview', 'sales']);
    }

    public function test_get_table_query_applies_active_widget_tab_callback(): void
    {
        // Arrange: spin up the component and activate the tab that mutates the query.
        $builder = Mockery::mock(Builder::class);
        $builder->shouldIgnoreMissing();
        $component = new WidgetTabTraitHarness($builder);
        $component->mount();
        $component->exposedSetActiveWidgetTab('sales');

        // Act: resolve the table query which should execute the widget tab modifier.
        $resolvedQuery = $component->runGetTableQuery();

        // Assert: the query callback ran and returned the original builder instance.
        expect($component->queryCallbacks)->toBe(['sales'])
            ->and($resolvedQuery)->toBe($builder);
    }

    public function test_refresh_widget_tab_records_delegates_to_reset_table(): void
    {
        // Arrange: initialise the component without needing to mount.
        $builder = Mockery::mock(Builder::class);
        $builder->shouldIgnoreMissing();
        $component = new WidgetTabTraitHarness($builder);

        // Act: trigger the refresh hook so the trait can reset the table state.
        $component->exposedRefreshWidgetTabRecords();

        // Assert: the harness recorded the reset action for verification.
        expect($component->resetCalls)->toBe(['reset']);
    }

    public function test_updated_active_tab_synchronises_widget_tab_property(): void
    {
        // Arrange: start with mismatched properties to confirm the sync behaviour.
        $builder = Mockery::mock(Builder::class);
        $builder->shouldIgnoreMissing();
        $component = new WidgetTabTraitHarness($builder);
        $component->mount();
        $component->activeTab = 'sales';

        // Act: invoke the legacy property update hook provided by Filament.
        $component->exposedUpdatedActiveTab();

        // Assert: both properties now point to the same widget tab selection.
        expect($component->activeWidgetTab)->toBe('sales');

        // Act: switch back through the canonical property and ensure the legacy alias follows along.
        $component->exposedUpdatedActiveWidgetTab('overview');

        // Assert: the legacy property mirrors the canonical widget tab value.
        expect($component->activeTab)->toBe('overview');
    }
}

abstract class FakeTableComponent
{
    public function __construct(protected Builder $baseQuery)
    {
        // Store the base query for reuse so the trait can layer its filters.
    }

    protected function getTableQuery(): Builder
    {
        // Return the base query untouched; the trait under test will clone and modify it.
        return $this->baseQuery;
    }
}

final class WidgetTabTraitHarness extends FakeTableComponent
{
    use HasWidgetTabs {
        applyWidgetTabFilters as public exposedApplyWidgetTabFilters;
        getActiveWidgetTabValue as public exposedGetActiveWidgetTabValue;
        getCachedWidgetTabs as public exposedGetCachedWidgetTabs;
        refreshWidgetTabRecords as public exposedRefreshWidgetTabRecords;
        setActiveWidgetTab as public exposedSetActiveWidgetTab;
        updatedActiveTab as public exposedUpdatedActiveTab;
        updatedActiveWidgetTab as public exposedUpdatedActiveWidgetTab;
    }

    /**
     * @var array<int, string>
     */
    public array $queryCallbacks = [];

    /**
     * @var array<int, string>
     */
    public array $resetCalls = [];

    public ?string $activeTab = null;

    public function runGetTableQuery(): Builder
    {
        // Expose the trait-provided table query hook for the test assertions.
        return $this->getTableQuery();
    }

    protected function shouldLoadDefaultActiveWidgetTab(): bool
    {
        // Enable default loading so the harness mirrors production behaviour.
        return true;
    }

    /**
     * @return array<string, WidgetTab>
     */
    public function getWidgetTabs(): array
    {
        // Provide two tabs: one passive and one that tracks query mutations.
        return [
            'overview' => WidgetTab::make('Overview')->value(10),
            'sales' => WidgetTab::make('Sales')->query(function (Builder $builder): Builder {
                $this->queryCallbacks[] = 'sales';

                return $builder;
            }),
        ];
    }

    protected function resetTable(): void
    {
        // Track reset calls so the tests can verify the delegation occurred.
        $this->resetCalls[] = 'reset';
    }
}
