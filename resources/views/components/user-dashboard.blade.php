@props([
    'user' => null,
    'title' => null,
    'subtitle' => null,
    'showStats' => true,
    'showRecentOrders' => true,
    'showWishlist' => true,
    'showAddresses' => true,
    'showProfile' => true,
])

@php
    $user = $user ?? auth()->user();
    $title = $title ?? __('My Dashboard');
    $subtitle = $subtitle ?? __('Welcome back, manage your account and orders');

    // Get user statistics
    $totalOrders = $user->orders()->count();
    $totalSpent = $user->orders()->sum('total');
    $wishlistCount = $user->wishlist()->count();
    $recentOrders = $user->orders()->latest()->take(5)->get();
@endphp

<div class="user-dashboard" x-data="userDashboard()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">{{ $title }}</h1>
            <p class="text-lg text-gray-600">{{ $subtitle }}</p>
        </div>

        {{-- Statistics Cards --}}
        @if ($showStats)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                {{-- Total Orders --}}
                <div
                     class="bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-medium transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">{{ __('Total Orders') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalOrders }}</p>
                        </div>
                    </div>
                </div>

                {{-- Total Spent --}}
                <div
                     class="bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-medium transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">{{ __('Total Spent') }}</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ \Illuminate\Support\Number::currency($totalSpent, current_currency(), app()->getLocale()) }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Wishlist Items --}}
                <div
                     class="bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-medium transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">{{ __('Wishlist Items') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $wishlistCount }}</p>
                        </div>
                    </div>
                </div>

                {{-- Account Status --}}
                <div
                     class="bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-medium transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">{{ __('Account Status') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ __('Active') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Quick Actions --}}
        <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('Quick Actions') }}</h2>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('orders.index', []) ?? '/orders' }}"
                   class="flex flex-col items-center p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                    <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                    <span class="text-sm font-medium text-gray-900">{{ __('View Orders') }}</span>
                </a>

                <a href="{{ route('wishlist.index', []) ?? '/wishlist' }}"
                   class="flex flex-col items-center p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                    <svg class="w-8 h-8 text-red-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                        </path>
                    </svg>
                    <span class="text-sm font-medium text-gray-900">{{ __('Wishlist') }}</span>
                </a>

                <a href="{{ route('addresses.index', []) ?? '/addresses' }}"
                   class="flex flex-col items-center p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                    <svg class="w-8 h-8 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z">
                        </path>
                    </svg>
                    <span class="text-sm font-medium text-gray-900">{{ __('Addresses') }}</span>
                </a>

                <a href="{{ route('profile.edit', []) ?? '/profile/edit' }}"
                   class="flex flex-col items-center p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                    <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-900">{{ __('Edit Profile') }}</span>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Recent Orders --}}
            @if ($showRecentOrders)
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">{{ __('Recent Orders') }}</h2>
                        <a href="{{ route('orders.index', []) ?? '/orders' }}"
                           class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                            {{ __('View All') }}
                        </a>
                    </div>

                    @if ($recentOrders->count() > 0)
                        <div class="space-y-4">
                            @foreach ($recentOrders as $order)
                                <div
                                     class="flex items-center justify-between p-4 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors duration-200">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                                </path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-900">{{ $order->order_number }}</h3>
                                            <p class="text-sm text-gray-600">
                                                {{ $order->created_at->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-gray-900">
                                            {{ \Illuminate\Support\Number::currency($order->total, current_currency(), app()->getLocale()) }}
                                        </p>
                                        <span
                                              class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            {{ $order->status === 'delivered'
                                                ? 'bg-green-100 text-green-800'
                                                : ($order->status === 'shipped'
                                                    ? 'bg-blue-100 text-blue-800'
                                                    : ($order->status === 'processing'
                                                        ? 'bg-yellow-100 text-yellow-800'
                                                        : 'bg-gray-100 text-gray-800')) }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                </path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('No orders yet') }}</h3>
                            <p class="text-gray-600 mb-4">{{ __('Start shopping to see your orders here') }}</p>
                            <a href="{{ localized_route('products.index') }}"
                               class="btn-gradient px-6 py-2 rounded-xl font-medium text-sm">
                                {{ __('Start Shopping') }}
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Account Information --}}
            @if ($showProfile)
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('Account Information') }}</h2>

                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <div
                                 class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                @if ($user->avatar)
                                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                                         class="w-16 h-16 rounded-full object-cover">
                                @else
                                    <span
                                          class="text-white font-semibold text-xl">{{ substr($user->name, 0, 1) }}</span>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $user->name }}</h3>
                                <p class="text-gray-600">{{ $user->email }}</p>
                                <p class="text-sm text-gray-500">{{ __('Member since') }}
                                    {{ $user->created_at->format('M Y') }}</p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-200">
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">{{ __('Phone') }}:</span>
                                    <p class="font-medium">{{ $user->phone ?? __('Not provided') }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-600">{{ __('Birthday') }}:</span>
                                    <p class="font-medium">
                                        {{ $user->birthday ? $user->birthday->format('M d, Y') : __('Not provided') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4">
                            <a href="{{ route('profile.edit', []) ?? '/profile/edit' }}"
                               class="w-full btn-gradient py-2 rounded-xl font-medium text-center block">
                                {{ __('Edit Profile') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Additional Sections --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
            {{-- Wishlist Preview --}}
            @if ($showWishlist && $user->wishlist()->count() > 0)
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">{{ __('Wishlist') }}</h2>
                        <a href="{{ route('wishlist.index', []) ?? '/wishlist' }}"
                           class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                            {{ __('View All') }}
                        </a>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        @foreach ($user->wishlist()->take(4) as $item)
                            <div
                                 class="border border-gray-200 rounded-xl p-3 hover:shadow-medium transition-shadow duration-200">
                                <div class="w-full h-24 bg-gray-100 rounded-lg mb-2 overflow-hidden">
                                    <img src="{{ $item->getFirstMediaUrl('images') ?? asset('images/placeholder-product.jpg') }}"
                                         alt="{{ $item->name }}" class="w-full h-full object-cover">
                                </div>
                                <h3 class="font-medium text-gray-900 text-sm line-clamp-2">{{ $item->name }}</h3>
                                <p class="text-sm font-semibold text-gray-900 mt-1">
                                    {{ \Illuminate\Support\Number::currency($item->price, current_currency(), app()->getLocale()) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Addresses --}}
            @if ($showAddresses)
                <div class="bg-white border border-gray-200 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">{{ __('Addresses') }}</h2>
                        <a href="{{ route('addresses.index', []) ?? '/addresses' }}"
                           class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                            {{ __('Manage') }}
                        </a>
                    </div>

                    @if ($user->addresses()->count() > 0)
                        <div class="space-y-3">
                            @foreach ($user->addresses()->take(2) as $address)
                                <div class="border border-gray-200 rounded-xl p-4">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="font-semibold text-gray-900">{{ $address->name }}</h3>
                                            <p class="text-sm text-gray-600 mt-1">
                                                {{ $address->address_line_1 }}, {{ $address->city }},
                                                {{ $address->country }}
                                            </p>
                                            @if ($address->is_default)
                                                <span
                                                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-2">
                                                    {{ __('Default') }}
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-xs text-gray-500 uppercase">{{ $address->type }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z">
                                </path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('No addresses yet') }}</h3>
                            <p class="text-gray-600 mb-4">{{ __('Add an address for faster checkout') }}</p>
                            <a href="{{ route('addresses.create', []) ?? '/addresses/create' }}"
                               class="btn-gradient px-6 py-2 rounded-xl font-medium text-sm">
                                {{ __('Add Address') }}
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    function userDashboard() {
        return {
            // Dashboard functionality can be added here
            init() {
                // Initialize dashboard features
            }
        }
    }
</script>
