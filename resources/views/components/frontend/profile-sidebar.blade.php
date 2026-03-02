<div class="space-y-1">
    <a href="{{ route('frontend.profile.index') }}" 
       class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors @if(request()->routeIs('frontend.profile.index*')) bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-400 @else text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white @endif">
        <svg class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 @if(request()->routeIs('frontend.profile.index*')) text-primary-700 dark:text-primary-400 @else text-gray-400 group-hover:text-gray-500 @endif" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
        </svg>
        <span class="truncate">{{ __('messages.Profile') }}</span>
    </a>

    <a href="{{ route('frontend.profile.edit') }}" 
       class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors @if(request()->routeIs('frontend.profile.edit*')) bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-400 @else text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white @endif">
        <svg class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 @if(request()->routeIs('frontend.profile.edit*')) text-primary-700 dark:text-primary-400 @else text-gray-400 group-hover:text-gray-500 @endif" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
        </svg>
        <span class="truncate">{{ __('messages.Edit') }}</span>
    </a>

    <a href="{{ route('frontend.profile.addresses') }}" 
       class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors @if(request()->routeIs('frontend.profile.addresses*')) bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-400 @else text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white @endif">
        <svg class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 @if(request()->routeIs('frontend.profile.addresses*')) text-primary-700 dark:text-primary-400 @else text-gray-400 group-hover:text-gray-500 @endif" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
        </svg>
        <span class="truncate">{{ __('messages.My addresses') }}</span>
    </a>

    <a href="{{ route('referrals.index') }}" 
       class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors @if(request()->routeIs('referrals.*')) bg-primary-50 text-primary-700 dark:bg-primary-900/50 dark:text-primary-400 @else text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white @endif">
        <svg class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 @if(request()->routeIs('referrals.*')) text-primary-700 dark:text-primary-400 @else text-gray-400 group-hover:text-gray-500 @endif" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
        </svg>
        <span class="truncate">{{ __('messages.referrals') }}</span>
    </a>
</div>

<div class="mt-8">
    <h3 class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider" id="settings-headline">
        {{ __('frontend.account.navigation.account') }}
    </h3>
    <div class="mt-1 space-y-1" aria-labelledby="settings-headline">
        <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
            @csrf
            <button type="submit" class="w-full group flex items-center px-3 py-2 text-sm font-medium text-red-600 rounded-md hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-900/20 dark:hover:text-red-300 transition-colors">
                <svg class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 text-red-500 group-hover:text-red-600 dark:text-red-400 dark:group-hover:text-red-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                </svg>
                <span class="truncate">{{ __('frontend.account.navigation.logout') }}</span>
            </button>
        </form>
    </div>
</div>

