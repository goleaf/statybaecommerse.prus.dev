<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Import Form -->
        <x-filament::section>
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('translations.import') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('admin.import_export.description') ?? 'Upload files to import data into the system.' }}</p>
            </div>

            <form wire:submit.prevent="import">
                {{ $this->form }}
                
                <div class="mt-6 flex gap-3">
                    <x-filament::button type="submit">
                        {{ __('translations.import') }}
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

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
