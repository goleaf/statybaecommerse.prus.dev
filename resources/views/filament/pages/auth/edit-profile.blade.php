<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Profile Header --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    @if(auth()->user()->avatar_url)
                        <img class="h-16 w-16 rounded-full object-cover" 
                             src="{{ auth()->user()->avatar_url }}" 
                             alt="{{ auth()->user()->name }}">
                    @else
                        <div class="h-16 w-16 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                            <span class="text-2xl font-bold text-gray-600 dark:text-gray-300">
                                {{ auth()->user()->initials }}
                            </span>
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ auth()->user()->full_name }}
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ auth()->user()->email }}
                    </p>
                    @if(auth()->user()->company)
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ auth()->user()->company }}
                            @if(auth()->user()->position)
                                - {{ auth()->user()->position }}
                            @endif
                        </p>
                    @endif
                </div>
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if(auth()->user()->is_active)
                            bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                        @else
                            bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                        @endif">
                        {{ auth()->user()->status_text }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Profile Form --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <form wire:submit="save">
                {{ $this->form }}

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex justify-end space-x-3">
                        <x-filament::button
                            type="button"
                            color="gray"
                            wire:click="cancel"
                        >
                            {{ __('admin.profile.cancel') }}
                        </x-filament::button>

                        <x-filament::button
                            type="submit"
                            color="primary"
                        >
                            {{ __('admin.profile.save_changes') }}
                        </x-filament::button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Additional Information --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Account Statistics --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ __('admin.profile.account_statistics') }}
                </h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.profile.orders_count') }}</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->orders_count }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.profile.total_spent') }}</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">€{{ number_format(auth()->user()->total_spent, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.profile.reviews_count') }}</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->reviews_count }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.profile.member_since') }}</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->created_at->format('M Y') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Security Information --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    {{ __('admin.profile.security_information') }}
                </h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.profile.email_verified') }}</dt>
                        <dd class="text-sm font-medium">
                            @if(auth()->user()->isEmailVerified())
                                <span class="text-green-600 dark:text-green-400">{{ __('admin.profile.verified') }}</span>
                            @else
                                <span class="text-red-600 dark:text-red-400">{{ __('admin.profile.not_verified') }}</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.profile.two_factor') }}</dt>
                        <dd class="text-sm font-medium">
                            @if(auth()->user()->hasTwoFactor())
                                <span class="text-green-600 dark:text-green-400">{{ __('admin.profile.enabled') }}</span>
                            @else
                                <span class="text-gray-600 dark:text-gray-400">{{ __('admin.profile.disabled') }}</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.profile.last_login') }}</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->diffForHumans() : __('admin.profile.never') }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.profile.roles') }}</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ auth()->user()->roles_label }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-filament-panels::page>
