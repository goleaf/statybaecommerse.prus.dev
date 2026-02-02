<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ number_format(\App\Models\Product::count()) }}</div>
                    <div class="text-sm text-gray-500">{{ __('messages.admin_products') }}</div>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ number_format(\App\Models\Category::count()) }}</div>
                    <div class="text-sm text-gray-500">{{ __('messages.admin_categories') }}</div>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ number_format(\App\Models\Brand::count()) }}</div>
                    <div class="text-sm text-gray-500">{{ __('messages.admin_brands') }}</div>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ number_format(\App\Models\User::count()) }}</div>
                    <div class="text-sm text-gray-500">{{ __('messages.users') }}</div>
                </div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ number_format(\App\Models\Order::count()) }}</div>
                    <div class="text-sm text-gray-500">{{ __('messages.admin_orders') }}</div>
                </div>
            </x-filament::section>
        </div>

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

        <!-- Import Guidelines -->
        <x-filament::section>
            <h3 class="text-lg font-semibold mb-4">{{ __('admin.import_export.guidelines') ?? 'Import Guidelines' }}</h3>
            <div class="space-y-3 text-sm text-gray-600">
                <div class="flex items-start gap-2">
                    <x-heroicon-o-information-circle class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" />
                    <span>{{ __('admin.import_export.guideline_1') ?? 'Ensure your XML file follows the supported schema.' }}</span>
                </div>
                <div class="flex items-start gap-2">
                    <x-heroicon-o-shield-check class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                    <span>{{ __('admin.import_export.guideline_2') ?? 'Uploaded files are scanned for security.' }}</span>
                </div>
                <div class="flex items-start gap-2">
                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-yellow-500 mt-0.5 flex-shrink-0" />
                    <span>{{ __('admin.import_export.guideline_3') ?? 'Large imports may take several minutes to process.' }}</span>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
