@if (count($widgetTabs = $this->getCachedWidgetTabs()))
    @php
        $activeWidgetTab = strval($this->activeWidgetTab);
        $renderHookScopes = $this->getRenderHookScopes();
    @endphp

    <div
        x-data="{
            widgetTab: $wire.$entangle('activeWidgetTab'),
            toggleWidgetTab(tabKey) {
                this.widgetTab = this.widgetTab === tabKey ? null : tabKey;
                $wire.resetTable();
            }
        }"
    >
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_TABS_START, scopes: $renderHookScopes) }}
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABS_START, scopes: $renderHookScopes) }}

        @php
            $gridClasses = [
                'fi-widget-tabs',
                'grid',
                'gap-4',
                'sm:grid-cols-2',
                'md:grid-cols-3',
                'xl:grid-cols-4',
            ];
        @endphp

        <div role="tablist" class="{{ implode(' ', $gridClasses) }}">
            @foreach ($widgetTabs as $widgetTabKey => $widgetTab)
                @php
                    $widgetTabKey = strval($widgetTabKey);
                    $alpineActive = "widgetTab === '{$widgetTabKey}'";
                    $attributes = $widgetTab->getExtraAttributeBag()
                        ->merge([
                            'x-on:click' => "toggleWidgetTab('{$widgetTabKey}')",
                            'role' => 'tab',
                            'aria-selected' => $activeWidgetTab === $widgetTabKey,
                        ])
                        ->class(array_merge(['fi-widget-tab'], $widgetTab->getThemeClasses()));
                    $value = $widgetTab->getValue();
                    $precision = $widgetTab->getPrecision();
                    $icon = $widgetTab->getIcon();
                    $iconSize = $widgetTab->getIconSize();
                    $isPercentage = $widgetTab->isPercentage();
                    $percentagePrecision = $widgetTab->getPercentagePrecision();
                    $label = $widgetTab->getLabel() ?? $this->generateWidgetTabLabel($widgetTabKey);
                @endphp

                <div
                    x-bind:class="{ 'fi-inactive': ! ({{ $alpineActive }}), 'fi-active': {{ $alpineActive }} }"
                    {{ $attributes }}
                >
                    <div class="flex items-center gap-x-6">
                        @if ($icon)
                            <div class="w-16 h-16 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center shadow-md">
                                <x-filament::icon
                                    :icon="$icon"
                                    @class([
                                        'fi-widget-tab-icon',
                                        match ($iconSize) {
                                            \Filament\Support\Enums\IconSize::Small, 'sm' => 'h-4 w-4',
                                            \Filament\Support\Enums\IconSize::Medium, 'md' => 'h-6 w-6',
                                            \Filament\Support\Enums\IconSize::Large, 'lg' => 'h-8 w-8',
                                            default => $iconSize,
                                        },
                                    ])
                                />
                            </div>
                        @endif
                    </div>
                    <div class="flex flex-col">
                        <span class="font-medium text-sm label">{{ $label }}</span>
                        <span class="text-2xl font-bold value">
                            {{ is_numeric($value)
                                ? ($isPercentage
                                    ? \Illuminate\Support\Number::percentage($value, $percentagePrecision)
                                    : \Illuminate\Support\Number::format($value, $precision))
                                : $value
                            }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_TABS_END, scopes: $renderHookScopes) }}
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABS_END, scopes: $renderHookScopes) }}
    </div>
@endif
