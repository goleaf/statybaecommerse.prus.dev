<x-filament-panels::page>
    <div class="space-y-6">
        @if(session()->has('impersonate'))
            <x-filament::card>
                <div class="flex items-center justify-between p-4 bg-warning-50 border border-warning-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-warning-600" />
                        <div>
                            <div class="font-semibold text-warning-800">{{ __('admin.impersonation.active_session') }}</div>
                            <div class="text-sm text-warning-700">
                                {{ __('admin.impersonation.currently_viewing_as', ['name' => auth()->user()->name]) }}
                            </div>
                        </div>
                    </div>
                    <div>
                        {{ $this->stopImpersonationAction }}
                    </div>
                </div>
            </x-filament::card>
        @endif

        <x-filament::card>
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('admin.impersonation.user_management') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('admin.impersonation.description') }}</p>
            </div>

            {{ $this->table }}
        </x-filament::card>

        <x-filament::card>
            <div class="space-y-4">
                <h3 class="text-md font-semibold text-gray-900">{{ __('admin.impersonation.guidelines') }}</h3>
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex items-start gap-2">
                        <x-heroicon-o-shield-check class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                        <span>{{ __('admin.impersonation.guideline_1') }}</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <x-heroicon-o-lock-closed class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" />
                        <span>{{ __('admin.impersonation.guideline_2') }}</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <x-heroicon-o-clock class="w-4 h-4 text-orange-500 mt-0.5 flex-shrink-0" />
                        <span>{{ __('admin.impersonation.guideline_3') }}</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <x-heroicon-o-eye class="w-4 h-4 text-purple-500 mt-0.5 flex-shrink-0" />
                        <span>{{ __('admin.impersonation.guideline_4') }}</span>
                    </div>
                </div>
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>
