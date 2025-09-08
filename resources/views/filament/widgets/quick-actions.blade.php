<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            {{ __('Quick Actions') }}
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($actions as $actionGroup)
                <div class="space-y-2">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ $actionGroup->getLabel() }}
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($actionGroup->getActions() as $action)
                            <x-filament::button
                                :href="$action->getUrl()"
                                :color="$action->getColor()"
                                size="sm"
                                :icon="$action->getIcon()"
                            >
                                {{ $action->getLabel() }}
                            </x-filament::button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>