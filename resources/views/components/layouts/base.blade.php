<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'E-Commerce') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles

    <!-- Additional head content -->
    {{ $head ?? '' }}
</head>

<body class="font-sans antialiased h-full bg-gray-50">
    <div class="min-h-full">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="flex-shrink-0">
                            <img class="h-8 w-auto" src="{{ asset('images/logo.svg') }}" alt="{{ config('app.name') }}">
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="{{ route('home') }}"
                           class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            {{ __('Home') }}
                        </a>
                        <a href="{{ route('products.index') }}"
                           class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            {{ __('Products') }}
                        </a>
                        <a href="{{ route('categories.index') }}"
                           class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            {{ __('Categories') }}
                        </a>
                        <a href="{{ route('brands.index') }}"
                           class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            {{ __('Brands') }}
                        </a>
                    </div>

                    <!-- Right side -->
                    <div class="flex items-center space-x-4">
                        <!-- Language Switcher -->
                        <div class="relative">
                            <select
                                    onchange="window.location.href = this.value"
                                    class="appearance-none bg-white border border-gray-300 rounded-md py-1 pl-3 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach (config('app.supported_locales', ['en']) as $locale)
                                    <option value="{{ route(Route::currentRouteName(), array_merge(request()->route()->parameters(), ['locale' => $locale])) }}"
                                            {{ app()->getLocale() === $locale ? 'selected' : '' }}>
                                        {{ strtoupper($locale) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Cart -->
                        <div class="relative">
                            <a href="{{ route('cart.index') }}"
                               class="text-gray-500 hover:text-gray-700 p-2 rounded-md">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m0 0h7M17 18a2 2 0 11-4 0 2 2 0 014 0zM9 18a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span id="cart-count"
                                      class="absolute -top-1 -right-1 bg-indigo-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                            </a>
                        </div>

                        <!-- User menu -->
                        @auth
                            <div class="relative">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-700">{{ auth()->user()->name }}</span>
                                    <a href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                       class="text-gray-500 hover:text-gray-700 text-sm">
                                        {{ __('Logout') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                        @csrf
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center space-x-4">
                                <a href="{{ route('login') }}"
                                   class="text-gray-500 hover:text-gray-700 text-sm font-medium">{{ __('Login') }}</a>
                                <a href="{{ route('register') }}"
                                   class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">{{ __('Register') }}</a>
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="col-span-1">
                        <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">{{ __('Company') }}
                        </h3>
                        <ul class="mt-4 space-y-4">
                            <li><a href="#"
                                   class="text-base text-gray-500 hover:text-gray-900">{{ __('About') }}</a></li>
                            <li><a href="#"
                                   class="text-base text-gray-500 hover:text-gray-900">{{ __('Contact') }}</a></li>
                            <li><a href="#"
                                   class="text-base text-gray-500 hover:text-gray-900">{{ __('Careers') }}</a></li>
                        </ul>
                    </div>
                    <div class="col-span-1">
                        <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">{{ __('Support') }}
                        </h3>
                        <ul class="mt-4 space-y-4">
                            <li><a href="#"
                                   class="text-base text-gray-500 hover:text-gray-900">{{ __('Help Center') }}</a></li>
                            <li><a href="#"
                                   class="text-base text-gray-500 hover:text-gray-900">{{ __('Shipping') }}</a></li>
                            <li><a href="#"
                                   class="text-base text-gray-500 hover:text-gray-900">{{ __('Returns') }}</a></li>
                        </ul>
                    </div>
                    <div class="col-span-1">
                        <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">{{ __('Legal') }}
                        </h3>
                        <ul class="mt-4 space-y-4">
                            <li><a href="#"
                                   class="text-base text-gray-500 hover:text-gray-900">{{ __('Privacy Policy') }}</a>
                            </li>
                            <li><a href="#"
                                   class="text-base text-gray-500 hover:text-gray-900">{{ __('Terms of Service') }}</a>
                            </li>
                            <li><a href="#"
                                   class="text-base text-gray-500 hover:text-gray-900">{{ __('Cookie Policy') }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-span-1">
                        <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase">{{ __('Newsletter') }}
                        </h3>
                        <p class="mt-4 text-base text-gray-500">
                            {{ __('Subscribe to get special offers and updates.') }}</p>
                        <form class="mt-4 flex">
                            <input type="email" placeholder="{{ __('Enter your email') }}"
                                   class="flex-1 min-w-0 px-4 py-2 border border-gray-300 rounded-l-md focus:ring-indigo-500 focus:border-indigo-500">
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-r-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ __('Subscribe') }}</button>
                        </form>
                    </div>
                </div>
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <p class="text-base text-gray-400 text-center">
                        © {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
                    </p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Notification Container -->
    <div id="notifications" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Notification Handler -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('notify', (event) => {
                const notification = event[0] || event;
                showNotification(notification.type, notification.message, notification.title);
            });

            Livewire.on('cart-updated', () => {
                updateCartCount();
            });
        });

        function showNotification(type, message, title = '') {
            const container = document.getElementById('notifications');
            const notification = document.createElement('div');

            const colors = {
                success: 'bg-green-50 border-green-200 text-green-800',
                error: 'bg-red-50 border-red-200 text-red-800',
                warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
                info: 'bg-blue-50 border-blue-200 text-blue-800'
            };

            notification.className =
                `max-w-sm w-full ${colors[type]} border rounded-lg shadow-lg p-4 transform transition-all duration-300 translate-x-full`;
            notification.innerHTML = `
                <div class="flex">
                    <div class="flex-1">
                        ${title ? `<div class="font-medium">${title}</div>` : ''}
                        <div class="text-sm">${message}</div>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-400 hover:text-gray-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;

            container.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }, 5000);
        }

        function updateCartCount() {
            // This would typically fetch from session or make an API call
            // For now, we'll just update based on session storage or a simple counter
            const cartItems = JSON.parse(sessionStorage.getItem('cart') || '[]');
            const count = cartItems.reduce((total, item) => total + item.quantity, 0);
            document.getElementById('cart-count').textContent = count;
        }

        // Initialize cart count on page load
        document.addEventListener('DOMContentLoaded', updateCartCount);
    </script>

    <!-- Additional scripts -->
    {{ $scripts ?? '' }}
</body>

</html>
