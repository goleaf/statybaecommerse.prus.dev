<x-layouts.base :title="$title ?? null">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="lg:grid lg:grid-cols-12 lg:gap-8">
            <aside class="hidden lg:block lg:col-span-3">
                <nav class="space-y-8 sticky top-6">
                    <div class="space-y-1">
                        <!-- Dashboard -->
                        <a href="{{ route('account.index') }}" 
                           class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors @if(request()->routeIs('account.index')) bg-gray-100 text-gray-900 @else text-gray-700 hover:bg-gray-50 hover:text-gray-900 @endif">
                            <svg class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 @if(request()->routeIs('account.index')) text-gray-700 @else text-gray-400 group-hover:text-gray-500 @endif" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                            </svg>
                            <span class="truncate">{{ __('frontend.account.navigation.dashboard') }}</span>
                        </a>

                        <!-- Orders -->
                        <a href="{{ route('account.orders') }}" 
                           class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors @if(request()->routeIs('account.orders*')) bg-gray-100 text-gray-900 @else text-gray-700 hover:bg-gray-50 hover:text-gray-900 @endif">
                            <svg class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 @if(request()->routeIs('account.orders*')) text-gray-700 @else text-gray-400 group-hover:text-gray-500 @endif" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                            <span class="truncate">{{ __('frontend.account.navigation.orders') }}</span>
                        </a>

                        <!-- Profile Details -->
                        <a href="{{ route('account.profile') }}" 
                           class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors @if(request()->routeIs('account.profile*')) bg-gray-100 text-gray-900 @else text-gray-700 hover:bg-gray-50 hover:text-gray-900 @endif">
                            <svg class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 @if(request()->routeIs('account.profile*')) text-gray-700 @else text-gray-400 group-hover:text-gray-500 @endif" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            <span class="truncate">{{ __('frontend.account.navigation.profile') }}</span>
                        </a>

                        <!-- Addresses -->
                        <a href="{{ route('account.addresses') }}" 
                           class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors @if(request()->routeIs('account.addresses*')) bg-gray-100 text-gray-900 @else text-gray-700 hover:bg-gray-50 hover:text-gray-900 @endif">
                            <svg class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 @if(request()->routeIs('account.addresses*')) text-gray-700 @else text-gray-400 group-hover:text-gray-500 @endif" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                            <span class="truncate">{{ __('frontend.account.navigation.addresses') }}</span>
                        </a>


                        <!-- Notifications -->
                        <a href="{{ route('account.notifications') }}" 
                           class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors @if(request()->routeIs('account.notifications*')) bg-gray-100 text-gray-900 @else text-gray-700 hover:bg-gray-50 hover:text-gray-900 @endif">
                            <svg class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 @if(request()->routeIs('account.notifications*')) text-gray-700 @else text-gray-400 group-hover:text-gray-500 @endif" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                            </svg>
                            <span class="truncate">{{ __('frontend.account.navigation.notifications') }}</span>
                        </a>

                        <!-- Referrals -->
                        <a href="{{ route('referrals.index') }}" 
                           class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors @if(request()->routeIs('referrals.*')) bg-gray-100 text-gray-900 @else text-gray-700 hover:bg-gray-50 hover:text-gray-900 @endif">
                            <svg class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 @if(request()->routeIs('referrals.*')) text-gray-700 @else text-gray-400 group-hover:text-gray-500 @endif" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                            <span class="truncate">{{ __('frontend.account.navigation.referrals') }}</span>
                        </a>
                    </div>

                    <div class="mt-8">
                        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider" id="settings-headline">
                            {{ __('frontend.account.navigation.account') }}
                        </h3>
                        <div class="mt-1 space-y-1" aria-labelledby="settings-headline">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full group flex items-center px-3 py-2 text-sm font-medium text-red-600 rounded-md hover:bg-red-50 hover:text-red-700 transition-colors">
                                    <svg class="flex-shrink-0 -ml-1 mr-3 h-6 w-6 text-red-500 group-hover:text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                    </svg>
                                    <span class="truncate">{{ __('frontend.account.navigation.logout') }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </nav>
            </aside>

            <main class="lg:col-span-9">
                <!-- Mobile Navigation -->
                <div class="lg:hidden mb-8" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            {{ __('frontend.account.navigation.menu') }}
                        </span>
                        <svg class="w-5 h-5 text-gray-400 transform transition-transform" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    
                    <div x-show="open" x-collapse x-cloak class="mt-2 bg-white rounded-lg border border-gray-200 p-4 text-left">
                        <!-- Same links for mobile -->
                        <div class="space-y-1">
                            <a href="{{ route('account.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors @if(request()->routeIs('account.index')) bg-gray-100 text-gray-900 @else text-gray-700 hover:bg-gray-50 hover:text-gray-900 @endif">
                                <span>{{ __('frontend.account.navigation.dashboard') }}</span>
                            </a>
                            <a href="{{ route('account.orders') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors @if(request()->routeIs('account.orders*')) bg-gray-100 text-gray-900 @else text-gray-700 hover:bg-gray-50 hover:text-gray-900 @endif">
                                <span>{{ __('frontend.account.navigation.orders') }}</span>
                            </a>
                            <a href="{{ route('account.profile') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors @if(request()->routeIs('account.profile*')) bg-gray-100 text-gray-900 @else text-gray-700 hover:bg-gray-50 hover:text-gray-900 @endif">
                                <span>{{ __('frontend.account.navigation.profile') }}</span>
                            </a>
                            <a href="{{ route('account.addresses') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors @if(request()->routeIs('account.addresses*')) bg-gray-100 text-gray-900 @else text-gray-700 hover:bg-gray-50 hover:text-gray-900 @endif">
                                <span>{{ __('frontend.account.navigation.addresses') }}</span>
                            </a>
                            <a href="{{ route('account.notifications') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors @if(request()->routeIs('account.notifications*')) bg-gray-100 text-gray-900 @else text-gray-700 hover:bg-gray-50 hover:text-gray-900 @endif">
                                <span>{{ __('frontend.account.navigation.notifications') }}</span>
                            </a>
                            <a href="{{ route('referrals.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors @if(request()->routeIs('referrals.*')) bg-gray-100 text-gray-900 @else text-gray-700 hover:bg-gray-50 hover:text-gray-900 @endif">
                                <span>{{ __('frontend.account.navigation.referrals') }}</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Page Content -->
                <div class="sm:overflow-hidden">
                    <div class="px-4 py-6 sm:p-8">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </div>
</x-layouts.base>



