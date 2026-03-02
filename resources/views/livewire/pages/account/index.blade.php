{{-- Account Index Page --}}
@php
$links = [
    [
        'title' => __('frontend.account.navigation.orders'),
        'description' => __('frontend.account.navigation.orders_description'),
        'href' => route('account.orders'),
        'icon' => 'M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
    ],
    [
        'title' => __('frontend.account.navigation.profile'),
        'description' => __('frontend.account.navigation.profile_description'),
        'href' => route('account.profile'),
        'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
    ],
    [
        'title' => __('frontend.account.navigation.addresses'),
        'description' => __('frontend.account.navigation.addresses_description'),
        'href' => route('account.addresses'),
        'icon' => 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z',
    ],
    [
        'title' => __('frontend.account.navigation.notifications'),
        'description' => __('frontend.account.notifications_description'),
        'href' => route('account.notifications'),
        'icon' => 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0',
    ],
    [
        'title' => __('frontend.account.navigation.referrals'),
        'description' => __('frontend.account.navigation.referrals_description'),
        'href' => route('referrals.index'),
        'icon' => 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z',
    ],
];
@endphp

<div class="space-y-6">
    <header class="border-b border-gray-200 pb-5 mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('frontend.account.navigation.dashboard') }}</h1>
        <p class="mt-2 text-sm text-gray-600">{{ __('frontend.account.navigation.dashboard_description') }}</p>
    </header>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($links as $link)
            <a href="{{ $link['href'] }}" class="group rounded-lg border border-gray-200 p-6 transition-colors hover:border-gray-300">
                <div class="mb-4 inline-flex items-center justify-center rounded-lg border border-gray-200 p-3 text-gray-600">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $link['icon'] }}" />
                    </svg>
                </div>
                
                <h3 class="mb-1 text-base font-semibold text-gray-900">{{ $link['title'] }}</h3>
                <p class="text-sm text-gray-500">{{ $link['description'] }}</p>
            </a>
        @endforeach
    </div>
</div>
