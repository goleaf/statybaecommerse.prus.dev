<?php

declare(strict_types=1);

namespace App\Filament\WidgetTabs\Concerns;

use App\Filament\WidgetTabs\Components\WidgetTab;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use ReflectionMethod;

trait HasWidgetTabs
{
    #[Url(as: 'activeTab')]
    public ?string $activeWidgetTab = null;

    /**
     * @var array<string|int, WidgetTab>
     */
    protected array $cachedWidgetTabs;

    public static function bootHasWidgetTabs(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABLE_BEFORE,
            fn (): View => view('filament.components.widget-tabs.resources.widget-tabs'),
            scopes: static::class,
        );
    }

    public function mount(): void
    {
        // Call the parent's mount implementation when it exists so we don't break
        // any Livewire lifecycle hooks that upstream pages rely on.
        if (method_exists(parent::class, 'mount')) {
            $parentMount = new ReflectionMethod(parent::class, 'mount');

            // Only invoke the parent mount if it doesn't expect required parameters to
            // avoid triggering argument count errors on components with custom mounts.
            if ($parentMount->getNumberOfRequiredParameters() === 0) {
                parent::mount();
            }
        }

        if ($this->shouldLoadDefaultActiveWidgetTab()) {
            $this->loadDefaultActiveWidgetTab();
        }

        // Keep both public properties aligned so external interactions stay backwards compatible.
        $this->synchroniseActiveTabAliases();
    }

    protected function shouldLoadDefaultActiveWidgetTab(): bool
    {
        return false;
    }

    protected function loadDefaultActiveWidgetTab(): void
    {
        if (filled($this->getActiveWidgetTabValue())) {
            return;
        }

        $this->setActiveWidgetTab($this->getDefaultActiveWidgetTab());
    }

    protected function synchroniseActiveTabAliases(): void
    {
        if (! $this->hasLegacyActiveTabProperty()) {
            return;
        }

        if ($this->activeWidgetTab === $this->activeTab) {
            return;
        }

        // Mirror the widget tab selection on the legacy `activeTab` property provided by Filament.
        $this->activeTab = $this->activeWidgetTab;
    }

    protected function getActiveWidgetTabValue(): string|int|null
    {
        if ($this->activeWidgetTab !== null) {
            return $this->activeWidgetTab;
        }

        if (! $this->hasLegacyActiveTabProperty()) {
            return null;
        }

        return $this->activeTab;
    }

    protected function setActiveWidgetTab(string|int|null $tab): void
    {
        // Normalise the tab key to a string so URL parameters and Livewire data stay in sync.
        $this->activeWidgetTab = $tab === null ? null : (string) $tab;

        if (! $this->hasLegacyActiveTabProperty()) {
            return;
        }

        // Keep the built-in Filament `activeTab` property updated for compatibility with existing code paths.
        $this->activeTab = $this->activeWidgetTab;
    }

    public function getDefaultActiveWidgetTab(): string|int|null
    {
        return array_key_first($this->getCachedWidgetTabs());
    }

    /**
     * @return array<string|int, WidgetTab>
     */
    public function getCachedWidgetTabs(): array
    {
        return $this->cachedWidgetTabs ??= $this->getWidgetTabs();
    }

    /**
     * @return array<string|int, WidgetTab>
     */
    public function getWidgetTabs(): array
    {
        return [];
    }

    public function generateWidgetTabLabel(string $key): string
    {
        return (string) str($key)
            ->replace(['_', '-'], ' ')
            ->ucfirst();
    }

    /**
     * Use an integer value, or an array with breakpoints and integer values.
     *
     * @return int|array<string, int>
     */
    public function getWidgetsPerRow(): int|array
    {
        return 3;
    }

    protected function modifyQueryWithActiveWidgetTab(Builder $query): Builder
    {
        $activeTab = $this->getActiveWidgetTabValue();

        if (blank($activeTab)) {
            return $query;
        }

        $widgetTabs = $this->getCachedWidgetTabs();

        if (! array_key_exists($activeTab, $widgetTabs)) {
            return $query;
        }

        return $widgetTabs[$activeTab]->modifyQuery($query);
    }

    protected function applyWidgetTabFilters(Builder $query): Builder
    {
        return $this->modifyQueryWithActiveWidgetTab($query);
    }

    protected function shouldRenderWidgetTabFilterIndicators(): bool
    {
        // Default to Filament's native behaviour unless a page explicitly opts into rendering filter chips.
        return false;
    }

    public function refreshWidgetTabRecords(): void
    {
        // Preserve the original tab switching behaviour for components that have not overridden the refresh hook.
        $this->resetTable();
    }

    public function updatedActiveWidgetTab(string|int|null $tab): void
    {
        // Keep the alias property synchronised without triggering unnecessary Livewire updates.
        if (! $this->hasLegacyActiveTabProperty()) {
            return;
        }

        if ($this->activeTab === ($tab === null ? null : (string) $tab)) {
            return;
        }

        $this->activeTab = $tab === null ? null : (string) $tab;
    }

    public function updatedActiveTab(): void
    {
        if (! $this->hasLegacyActiveTabProperty()) {
            return;
        }

        // Ensure direct mutations to `activeTab` update the canonical widget tab property too.
        $normalisedTab = $this->activeTab === null ? null : (string) $this->activeTab;

        if ($this->activeWidgetTab === $normalisedTab) {
            return;
        }

        $this->activeWidgetTab = $normalisedTab;
    }

    protected function getTableQuery(): Builder
    {
        return $this->applyWidgetTabFilters(parent::getTableQuery());
    }

    protected function hasLegacyActiveTabProperty(): bool
    {
        // The base Filament ListRecords class exposes the `activeTab` property, but we
        // defensively check for its presence so the trait fails gracefully if reused
        // on a component that doesn't ship with the legacy attribute.
        return property_exists($this, 'activeTab');
    }
}
