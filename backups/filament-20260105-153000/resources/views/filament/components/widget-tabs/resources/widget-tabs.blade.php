@if (count($widgetTabs = $this->getCachedWidgetTabs()))
    @php
        $activeWidgetTab = strval($this->activeWidgetTab);
        $renderHookScopes = $this->getRenderHookScopes();

        // Normalise the responsive column configuration so we can compute CSS classes and variables.
        $widgetsPerRow = $this->getWidgetsPerRow();
        if (! is_array($widgetsPerRow)) {
            $widgetsPerRow = [
                'md' => $widgetsPerRow,
            ];
        }

        $columnConfig = collect([
            'default' => 1,
            'sm' => 2,
            'md' => 3,
            'lg' => null,
            'xl' => null,
            '2xl' => null,
        ])->merge($widgetsPerRow);

        $gridClasses = $columnConfig
            ->filter()
            ->keys()
            ->map(static function (string $key): string {
                $string = str($key);

                return ($key === 'default'
                    ? $string->replace($key, 'grid-cols-[--cols-')
                    : $string->append(':grid-cols-[--cols-')
                )
                    ->append($key)
                    ->finish(']')
                    ->value();
            })
            ->all();

        $styleVariables = $columnConfig
            ->filter()
            ->mapWithKeys(static function (int $columns, string $key): array {
                $variable = $key === 'default' ? '--cols-default' : sprintf('--cols-%s', $key);

                return [
                    $variable => sprintf('repeat(%d, minmax(0, 1fr))', $columns),
                ];
            })
            ->all();

        $styleAttribute = collect($styleVariables)
            ->map(static fn (string $value, string $variable): string => sprintf('%s: %s', $variable, $value))
            ->implode('; ');
    @endphp

    <div
        x-data="{
            widgetTab: $wire.$entangle('activeWidgetTab'),
            toggleWidgetTab(tabKey) {
                this.widgetTab = this.widgetTab === tabKey ? null : tabKey;
                // Ask the Livewire component to refresh the table without discarding active filters.
                $wire.refreshWidgetTabRecords();
            }
        }"
    >
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_TABS_START, scopes: $renderHookScopes) }}
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABS_START, scopes: $renderHookScopes) }}

        @php
            $filterIndicators = [];

            if (method_exists($this, 'getTable') && $this->shouldRenderWidgetTabFilterIndicators()) {
                // Capture the active filter indicators so we can surface them as persistent chips beneath the tabs.
                $filterIndicators = $this->getTable()->getFilterIndicators();
            }
        @endphp

        <div
            role="tablist"
            class="fi-widget-tabs grid gap-4 {{ implode(' ', $gridClasses) }}"
            style="{{ $styleAttribute }}"
        >
            @foreach ($widgetTabs as $widgetTabKey => $widgetTab)
                @php
                    $widgetTabKey = strval($widgetTabKey);
                    $alpineCondition = "widgetTab === '{$widgetTabKey}'";

                    $tabClasses = array_values(array_filter([
                        'fi-widget-tab',
                        ...$widgetTab->getThemeClasses(),
                    ]));

                    $attributeBag = $widgetTab->getExtraAttributeBag()
                        ->class($tabClasses)
                        ->merge([
                            'x-on:click' => "toggleWidgetTab('{$widgetTabKey}')",
                            'x-bind:class' => "{ 'fi-active': {$alpineCondition}, 'fi-inactive': !({$alpineCondition}) }",
                        ]);

                    $value = $widgetTab->getValue();
                    $precision = $widgetTab->getPrecision();
                    $percentagePrecision = $widgetTab->getPercentagePrecision();
                    $isPercentage = $widgetTab->isPercentage();
                    $label = $widgetTab->getLabel() ?? $this->generateWidgetTabLabel($widgetTabKey);
                    $icon = $widgetTab->getIcon();
                    $iconSize = $widgetTab->getIconSize();

                    // Format the primary metric shown on each tab without mutating the underlying record.
                    $displayValue = $value;
                    if (is_numeric($value)) {
                        $decimals = $isPercentage ? (int) $percentagePrecision : (int) $precision;
                        $displayValue = number_format((float) $value, max($decimals, 0));

                        if ($isPercentage) {
                            $displayValue .= '%';
                        }
                    }

                    // Align icon sizing with Filament's enum so we match the existing dashboard widgets.
                    $resolvedIconSize = $iconSize instanceof \Filament\Support\Enums\IconSize ? $iconSize : \Filament\Support\Enums\IconSize::tryFrom((string) $iconSize);
                    $iconClasses = match ($resolvedIconSize) {
                        \Filament\Support\Enums\IconSize::Small => 'h-4 w-4',
                        \Filament\Support\Enums\IconSize::Large => 'h-8 w-8',
                        default => 'h-6 w-6',
                    };
                @endphp

                <div {{ $attributeBag }}>
                    @if ($icon)
                        {{-- Optional icon rendering for clearer visual cues. --}}
                        <div class="flex h-full items-center">
                            <x-filament::icon :icon="$icon" class="{{ $iconClasses }}" />
                        </div>
                    @endif

                    <div class="flex flex-col justify-center">
                        @if ($label)
                            {{-- Emphasise the label so that translated strings remain visible. --}}
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ $label }}
                            </span>
                        @endif

                        <span class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ $displayValue }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($filterIndicators !== [])
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($filterIndicators as $indicator)
                    @php
                        $removeHandler = $indicator->getRemoveLivewireClickHandler();
                        $isRemovable = $indicator->isRemovable();
                    @endphp

                    <span class="inline-flex items-center gap-2 rounded-full bg-primary-100 px-3 py-1 text-sm font-medium text-primary-700 dark:bg-primary-500\/10 dark:text-primary-300">
                        {{ $indicator->getLabel() }}

                        @if ($isRemovable && filled($removeHandler))
                            <button
                                type="button"
                                wire:click="{{ $removeHandler }}"
                                class="-mr-1 inline-flex h-5 w-5 items-center justify-center rounded-full transition hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:hover:bg-primary-500\/20"
                            >
                                <x-filament::icon icon="heroicon-o-x-mark" class="h-4 w-4" />
                            </button>
                        @endif
                    </span>
                @endforeach
            </div>
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_TABS_END, scopes: $renderHookScopes) }}
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABS_END, scopes: $renderHookScopes) }}
    </div>
@endif
