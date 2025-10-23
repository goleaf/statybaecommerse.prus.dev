<?php

declare(strict_types=1);

namespace App\Filament\WidgetTabs\Concerns;

use App\Filament\WidgetTabs\Components\WidgetTab;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;
use Livewire\Attributes\Url;

trait HasWidgetTabs
{
    #[Url(as: 'activeTab')]
    public ?string $activeWidgetTab = null;

    /**
     * Mirror the widget tab selection for legacy callers and tests that refer to the property as `activeTab`.
     */
    public ?string $activeTab = null;

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
        parent::mount();

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
        if ($this->activeWidgetTab === $this->activeTab) {
            return;
        }

        $this->activeTab = $this->activeWidgetTab;
    }

    protected function getActiveWidgetTabValue(): string|int|null
    {
        return $this->activeWidgetTab ?? $this->activeTab;
    }

    protected function setActiveWidgetTab(string|int|null $tab): void
    {
        $this->activeWidgetTab = $tab === null ? null : (string) $tab;
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

    public function updatedActiveWidgetTab(string|int|null $tab): void
    {
        // Keep the alias property synchronised without triggering unnecessary Livewire updates.
        if ($this->activeTab === ($tab === null ? null : (string) $tab)) {
            return;
        }

        $this->activeTab = $tab === null ? null : (string) $tab;
    }

    public function updatedActiveTab(): void
    {
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
}
