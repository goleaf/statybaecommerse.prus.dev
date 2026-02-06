<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                {{ __('ui.csv_imports') }}
            </x-slot>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                @foreach($this->getCsvImportPages() as $page)
                    <x-filament::button :href="$page['url']" tag="a" color="gray">
                        {{ $page['label'] }}
                    </x-filament::button>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
