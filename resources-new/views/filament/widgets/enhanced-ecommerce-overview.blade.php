<x-filament-widgets::widget>
    <x-filament::section>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($this->getStats() as $stat)
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm">
                    {{ $stat }}
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
