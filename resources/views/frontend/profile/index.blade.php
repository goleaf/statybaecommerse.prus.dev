<x-frontend.profile-layout title="{{ __('messages.Profile') }}">
    <div class="space-y-8">
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 dark:border-white/10 pb-5 mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.Profile') }}</h1>
            <a href="{{ route('frontend.profile.edit') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                </svg>
                {{ __('messages.Edit') }}
            </a>
        </header>

        <section>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('frontend.profile.overview.personal_details') }}</h2>
            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-6 border border-gray-100 dark:border-white/5">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6 text-sm">
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.Name') }}</dt>
                        <dd class="text-gray-900 dark:text-white font-medium">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('frontend.profile.fields.email_label') }}</dt>
                        <dd class="text-gray-900 dark:text-white font-medium">{{ $user->email }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="mt-10">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.My addresses') }}</h2>
                <a href="{{ route('frontend.profile.addresses') }}" class="text-sm font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300">
                    {{ __('frontend.profile.actions.manage_addresses') }} &rarr;
                </a>
            </div>
            
            @if ($user->addresses->isEmpty())
                <div class="text-center py-8 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-white/5 border-dashed">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('frontend.profile.addresses.empty_short') }}</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($user->addresses as $address)
                        <div class="p-5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-lg shadow-sm relative hover:border-primary-300 dark:hover:border-primary-500/50 transition-colors">
                            @if ($address->is_default)
                                <span class="absolute top-4 right-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-300">
                                    {{ __('messages.Default') }}
                                </span>
                            @endif
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1 pr-16">{{ $address->full_name ?? ($address->first_name.' '.$address->last_name) }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ $address->full_address }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-frontend.profile-layout>
