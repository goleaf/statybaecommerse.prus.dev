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

        <!-- Import/Export Form -->
        <x-filament::section>
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('translations.data_import_export') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('translations.page_description') }}</p>
            </div>

            <form wire:submit="import">
                {{ $this->form }}
                
                <div class="mt-6 flex gap-3">
                    <x-filament::button type="button" wire:click="mountAction('import')">
                        {{ __('translations.import') }}
                    </x-filament::button>
                    <x-filament::button type="button" color="gray" wire:click="mountAction('export')">
                        {{ __('translations.export') }}
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        <!-- Recent Export Files -->
        <x-filament::section>
            <h3 class="text-lg font-semibold mb-4">{{ __('translations.recent_addresses') }} (Exports)</h3>
            <div class="space-y-3">
                @php
                    $disk = \Storage::disk(\App\Support\Storage\SecureStorage::disk());
                    $files = $disk->exists('exports') ? $disk->files('exports') : [];
                    $recentFiles = array_slice(array_reverse($files), 0, 5);
                @endphp
                
                @forelse($recentFiles as $file)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <x-heroicon-o-document class="w-5 h-5 text-gray-500" />
                            <div>
                                <div class="font-medium">{{ basename($file) }}</div>
                                <div class="text-sm text-gray-500">
                                    {{ __('translations.file_size') ?? 'Size' }}: {{ $disk->size($file) }} bytes
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                              <a
                                  href="{{ \App\Support\Storage\SecureStorage::temporarySignedUrl($file, now()->addMinutes(30), true) }}"
                                target="_blank"
                                class="inline-flex items-center gap-1 px-3 py-1 text-sm font-medium text-blue-600 hover:text-blue-700"
                            >
                                <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                {{ __('translations.download') }}
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-gray-500">
                        <x-heroicon-o-folder-open class="w-8 h-8 mx-auto mb-2" />
                        <div>No recent exports found</div>
                    </div>
                @endforelse
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>