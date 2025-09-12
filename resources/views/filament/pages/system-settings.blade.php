<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ __('system_settings.navigation_label') }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('system_settings.page_description') }}
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ __('system_settings.total_settings') }}: {{ \App\Models\SystemSetting::count() }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ __('system_settings.active_settings') }}: {{ \App\Models\SystemSetting::active()->count() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Settings Form -->
        <div class="bg-white shadow rounded-lg">
            <form wire:submit="saveSettings">
                {{ $this->form }}
                
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                    <button type="button" 
                            wire:click="resetSettings"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        {{ __('system_settings.reset_settings') }}
                    </button>
                    
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ __('system_settings.save_settings') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                {{ __('system_settings.quick_actions') }}
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button type="button"
                        wire:click="exportSettings"
                        class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ __('system_settings.export_settings') }}
                </button>
                
                <button type="button"
                        wire:click="$dispatch('open-modal', { id: 'import-settings' })"
                        class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                    {{ __('system_settings.import_settings') }}
                </button>
                
                <button type="button"
                        wire:click="clearCache"
                        class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    {{ __('system_settings.clear_cache') }}
                </button>
            </div>
        </div>

        <!-- Settings Statistics -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                {{ __('system_settings.statistics') }}
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">
                        {{ \App\Models\SystemSetting::count() }}
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ __('system_settings.total_settings') }}
                    </div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">
                        {{ \App\Models\SystemSetting::active()->count() }}
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ __('system_settings.active_settings') }}
                    </div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-yellow-600">
                        {{ \App\Models\SystemSetting::public()->count() }}
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ __('system_settings.public_settings') }}
                    </div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">
                        {{ \App\Models\SystemSetting::where('is_encrypted', true)->count() }}
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ __('system_settings.encrypted_settings') }}
                    </div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">
                        {{ \App\Models\SystemSettingCategory::active()->count() }}
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ __('system_settings.categories') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <x-filament::modal id="import-settings" :close-by-clicking-away="false">
        <x-slot name="heading">
            {{ __('system_settings.import_settings') }}
        </x-slot>
        
        <x-slot name="description">
            {{ __('system_settings.import_description') }}
        </x-slot>
        
        <form wire:submit="importSettings">
            <div class="space-y-4">
                <x-filament::input.wrapper>
                    <x-filament::input type="file" 
                                       wire:model="importFile" 
                                       accept=".json"
                                       required />
                </x-filament::input.wrapper>
            </div>
            
            <x-slot name="footerActions">
                <x-filament::button type="submit" color="primary">
                    {{ __('system_settings.import') }}
                </x-filament::button>
                
                <x-filament::button color="gray" 
                                    x-on:click="close">
                    {{ __('system_settings.cancel') }}
                </x-filament::button>
            </x-slot>
        </form>
    </x-filament::modal>
</x-filament-panels::page>
