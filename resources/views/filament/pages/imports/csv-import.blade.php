<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ $this->getTitle() }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Upload a CSV file and map the columns to import data.') }}
                </p>
            </div>

            <form wire:submit.prevent="import">
                {{ $this->form }}

                <div class="mt-6 flex flex-wrap gap-3">
                    <x-filament::button type="submit">
                        {{ __('translations.import') }}
                    </x-filament::button>
                    <x-filament::button type="button" color="gray" wire:click="downloadExample">
                        {{ __('filament-actions::import.modal.actions.download_example.label') }}
                    </x-filament::button>
                    <x-filament::button color="gray" :href="\App\Filament\Pages\DataImportExport::getUrl()" tag="a">
                        {{ __('Back to imports') }}
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        @if($this->lastImport)
            <x-filament::section>
                <x-slot name="heading">
                    {{ __('Last import summary') }}
                </x-slot>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ __('Total rows') }}
                        </div>
                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format($this->lastImport['total']) }}
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ __('Processed rows') }}
                        </div>
                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format($this->lastImport['processed']) }}
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ __('Successful rows') }}
                        </div>
                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format($this->lastImport['successful']) }}
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            {{ __('Failed rows') }}
                        </div>
                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            {{ number_format($this->lastImport['failed']) }}
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
