<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">

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
        <!-- Header -->
        <x-layouts.header />

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>

        <!-- Footer -->
        <x-layouts.footer />
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
            // Update cart counters safely without assuming elements exist
            try {
                const cartItems = JSON.parse(sessionStorage.getItem('cart') || '[]');
                const count = cartItems.reduce((total, item) => total + (Number(item.quantity) || 0), 0);

                const el = document.getElementById('cart-count');
                if (el) {
                    el.textContent = String(count);
                    el.style.display = count > 0 ? 'inline' : 'none';
                }

                const counters = document.querySelectorAll('[data-cart-count]');
                counters.forEach((node) => {
                    node.textContent = String(count);
                    if (node instanceof HTMLElement) {
                        node.style.display = count > 0 ? 'inline' : 'none';
                    }
                });
            } catch (e) {
                // Silently ignore to avoid breaking pages without cart UI
            }
        }

        // Initialize cart count on page load
        document.addEventListener('DOMContentLoaded', updateCartCount);
    </script>

    <!-- Additional scripts -->
    {{ $scripts ?? '' }}
</body>

</html>
