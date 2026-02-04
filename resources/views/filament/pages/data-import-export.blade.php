<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Import Form -->
        <x-filament::section>
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('translations.import') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('admin.import_export.description') ?? 'Upload files to import data into the system.' }}</p>
            </div>

            <form wire:submit="import">
                {{ $this->form }}
                
                <div class="mt-6 flex gap-3">
                    <x-filament::button type="button" wire:click="mountAction('import')">
                        {{ __('translations.import') }}
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
