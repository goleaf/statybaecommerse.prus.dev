@props(['title' => __('messages.Profile')])

<x-layouts.base :title="$title">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="lg:grid lg:grid-cols-12 lg:gap-8">
            <aside class="hidden lg:block lg:col-span-3">
                <nav class="space-y-8 sticky top-6">
                    <x-frontend.profile-sidebar />
                </nav>
            </aside>

            <main class="lg:col-span-9">
                <!-- Mobile Navigation (Visible only on small screens) -->
                <div class="lg:hidden mb-8" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            {{ __('frontend.home') ?? 'Profile Navigation' }}
                        </span>
                        <svg class="w-5 h-5 text-gray-400 transform transition-transform" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                    
                    <div x-show="open" x-collapse x-cloak class="mt-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-white/10 shadow-sm p-4 text-left">
                        <x-frontend.profile-sidebar />
                    </div>
                </div>

                <!-- Page Content -->
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm sm:overflow-hidden">
                    <div class="px-4 py-6 sm:p-8">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </div>
</x-layouts.base>
