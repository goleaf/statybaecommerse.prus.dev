@php(/** @var \App\Filament\Pages\DataImportExport $this */ null)

<x-filament::page>
    <div class="space-y-6">
        {{ $this->form }}
        <div class="flex gap-3">
            <x-filament::button wire:click="callAction('import')">
                {{ __('translations.import') }}
            </x-filament::button>
            <x-filament::button color="gray" wire:click="callAction('export')">
                {{ __('translations.export') }}
            </x-filament::button>
        </div>
    </div>
</x-filament::page>

<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-filament::card>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ number_format(\App\Models\Product::count()) }}</div>
                    <div class="text-sm text-gray-500">{{ __('admin.models.products') }}</div>
                </div>
            </x-filament::card>
            
            <x-filament::card>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ number_format(\App\Models\Category::count()) }}</div>
                    <div class="text-sm text-gray-500">{{ __('admin.models.categories') }}</div>
                </div>
            </x-filament::card>
            
            <x-filament::card>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ number_format(\App\Models\Brand::count()) }}</div>
                    <div class="text-sm text-gray-500">{{ __('admin.models.brands') }}</div>
                </div>
            </x-filament::card>
            
            <x-filament::card>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ number_format(\App\Models\User::where('is_admin', false)->count()) }}</div>
                    <div class="text-sm text-gray-500">{{ __('admin.models.customers') }}</div>
                </div>
            </x-filament::card>
            
            <x-filament::card>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ number_format(\App\Models\Order::count()) }}</div>
                    <div class="text-sm text-gray-500">{{ __('admin.models.orders') }}</div>
                </div>
            </x-filament::card>
        </div>

        <!-- Import/Export Form -->
        <x-filament::card>
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('admin.import_export.data_management') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('admin.import_export.description') }}</p>
            </div>

            {{ $this->form }}
        </x-filament::card>

        <!-- Recent Import/Export History -->
        <x-filament::card>
            <h3 class="text-lg font-semibold mb-4">{{ __('admin.import_export.recent_operations') }}</h3>
            <div class="space-y-3">
                @php
                    $recentFiles = \Storage::disk('public')->files('exports');
                    $recentFiles = array_slice(array_reverse($recentFiles), 0, 5);
                @endphp
                
                @forelse($recentFiles as $file)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <x-heroicon-o-document class="w-5 h-5 text-gray-500" />
                            <div>
                                <div class="font-medium">{{ basename($file) }}</div>
                                <div class="text-sm text-gray-500">
                                    {{ __('admin.import_export.file_size') }}: {{ \Storage::disk('public')->size($file) }} bytes
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a 
                                href="{{ asset('storage/' . $file) }}" 
                                target="_blank"
                                class="inline-flex items-center gap-1 px-3 py-1 text-sm font-medium text-blue-600 hover:text-blue-700"
                            >
                                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                {{ __('admin.actions.download') }}
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-gray-500">
                        <x-heroicon-o-folder-open class="w-8 h-8 mx-auto mb-2" />
                        <div>{{ __('admin.import_export.no_recent_files') }}</div>
                    </div>
                @endforelse
            </div>
        </x-filament::card>

        <!-- Import/Export Guidelines -->
        <x-filament::card>
            <h3 class="text-lg font-semibold mb-4">{{ __('admin.import_export.guidelines') }}</h3>
            <div class="space-y-3 text-sm text-gray-600">
                <div class="flex items-start gap-2">
                    <x-heroicon-o-information-circle class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" />
                    <span>{{ __('admin.import_export.guideline_1') }}</span>
                </div>
                <div class="flex items-start gap-2">
                    <x-heroicon-o-shield-check class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                    <span>{{ __('admin.import_export.guideline_2') }}</span>
                </div>
                <div class="flex items-start gap-2">
                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-yellow-500 mt-0.5 flex-shrink-0" />
                    <span>{{ __('admin.import_export.guideline_3') }}</span>
                </div>
                <div class="flex items-start gap-2">
                    <x-heroicon-o-clock class="w-4 h-4 text-purple-500 mt-0.5 flex-shrink-0" />
                    <span>{{ __('admin.import_export.guideline_4') }}</span>
                </div>
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>



