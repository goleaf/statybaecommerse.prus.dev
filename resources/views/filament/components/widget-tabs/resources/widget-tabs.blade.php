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

        <x-filament.components::widget-tabs.index>
            @foreach ($widgetTabs as $widgetTabKey => $widgetTab)
                @php
                    $widgetTabKey = strval($widgetTabKey);
                @endphp

                <x-filament.components::widget-tabs.item
                    :alpine-active="'widgetTab === \'' . $widgetTabKey . '\''"
                    x-on:click="toggleWidgetTab('{{ $widgetTabKey }}')"
                    :value="$widgetTab->getValue()"
                    :precision="$widgetTab->getPrecision()"
                    :icon="$widgetTab->getIcon()"
                    :icon-size="$widgetTab->getIconSize()"
                    :is-percentage="$widgetTab->isPercentage()"
                    :percentage-precision="$widgetTab->getPercentagePrecision()"
                    :label="$widgetTab->getLabel() ?? $this->generateWidgetTabLabel($widgetTabKey)"
                    :theme-classes="$widgetTab->getThemeClasses()"
                    :attributes="$widgetTab->getExtraAttributeBag()"
                />
            @endforeach
        </x-filament.components.widget-tabs.index>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_TABS_END, scopes: $renderHookScopes) }}
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::RESOURCE_PAGES_LIST_RECORDS_TABS_END, scopes: $renderHookScopes) }}
    </div>
@endif
