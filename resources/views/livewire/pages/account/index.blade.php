{{-- Account Index Page --}}
@php
$links = [
    [
        'title' => __('messages.frontend') . ' (Orders)',
        'description' => __('messages.frontend'),
        'href' => route('account.orders'),
        'icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
    ],
    [
        'title' => __('messages.frontend') . ' (Profile)',
        'description' => __('messages.frontend'),
        'href' => route('account.profile'),
        'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
    ],
    [
        'title' => __('messages.frontend') . ' (Addresses)',
        'description' => __('messages.frontend'),
        'href' => route('account.addresses'),
        'icon' => 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z',
    ],
    [
        'title' => __('messages.frontend') . ' (Documents)',
        'description' => __('messages.frontend'),
        'href' => route('account.documents'),
        'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
    ],
    [
        'title' => __('messages.frontend') . ' (Notifications)',
        'description' => __('messages.frontend'),
        'href' => route('account.notifications'),
        'icon' => 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0',
    ],
    [
        'title' => __('messages.referrals'),
        'description' => 'Invite friends and earn rewards',
        'href' => route('referrals.index'),
        'icon' => 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z',
    ],
];
@endphp

<div class="space-y-6">
    <header class="border-b border-gray-200 dark:border-white/10 pb-5 mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.frontend') }} (Dashboard)</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Manage your account settings, orders, and addresses.</p>
    </header>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($links as $link)
            <a href="{{ $link['href'] }}" class="group relative bg-white dark:bg-gray-800/50 rounded-xl p-6 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-1 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-primary-50/50 to-transparent dark:from-primary-900/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                
                <div class="relative z-10">
                    <div class="inline-flex items-center justify-center p-3 bg-primary-50 dark:bg-primary-900/30 rounded-lg mb-4 text-primary-600 dark:text-primary-400 group-hover:bg-primary-100 dark:group-hover:bg-primary-800/50 transition-colors">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}" />
                        </svg>
                    </div>
                    
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ $link['title'] }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $link['description'] }}</p>
                </div>
            </a>
        @endforeach
    </div>
</div>
