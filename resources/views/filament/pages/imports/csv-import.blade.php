<x-filament-panels::page>
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                {{ $this->getTitle() }}
            </h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ __('admin.advanced_import_description') }}
            </p>
        </div>

        <form wire:submit.prevent="import">
            {{ $this->form }}
        </form>

        <div class="flex items-center gap-3">
            <x-filament::button type="button" color="gray" wire:click="downloadExample" icon="heroicon-o-document-arrow-down">
                {{ __('filament-actions::import.modal.actions.download_example.label') }}
            </x-filament::button>
            <x-filament::button color="gray" :href="\App\Filament\Pages\DataImportExport::getUrl()" tag="a" icon="heroicon-o-arrow-left">
                {{ __('admin.actions.back') ?? __('Back to imports') }}
            </x-filament::button>
        </div>

        @if($this->lastImport)
            <x-filament::section collapsible>
                <x-slot name="heading">
                    {{ __('admin.last_import_summary') ?? __('Last import summary') }}
                </x-slot>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 font-medium">
                            {{ __('admin.import_total_rows') }}
                        </div>
                        <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($this->lastImport['total']) }}
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 font-medium">
                            {{ __('admin.import_processed_rows') ?? __('Processed rows') }}
                        </div>
                        <div class="mt-1 text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ number_format($this->lastImport['processed']) }}
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 font-medium text-success-600">
                            {{ __('admin.import_successful_rows') ?? __('Successful rows') }}
                        </div>
                        <div class="mt-1 text-2xl font-bold text-success-600">
                            {{ number_format($this->lastImport['successful']) }}
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                        <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 font-medium text-danger-600">
                            {{ __('admin.import_failed_rows') ?? __('Failed rows') }}
                        </div>
                        <div class="mt-1 text-2xl font-bold text-danger-600">
                            {{ number_format($this->lastImport['failed']) }}
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>