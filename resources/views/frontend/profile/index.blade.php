@extends('frontend.layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-10 space-y-8">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ __('My profile') }}</h1>
            <p class="text-slate-600 dark:text-slate-300">{{ __('Manage your personal details, orders, and saved addresses.') }}</p>
        </header>

        @if (session('status'))
            <div class="rounded-lg bg-green-100 text-green-800 px-4 py-3">{{ session('status') }}</div>
        @endif

        <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <article class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Account summary') }}</h2>
                <dl class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                    <div class="flex justify-between">
                        <dt>{{ __('Orders placed') }}</dt>
                        <dd>{{ $stats['orders_count'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>{{ __('Total spent') }}</dt>
                        <dd>{{ number_format($stats['total_spent'], 2) }} {{ config('app.currency', 'EUR') }}</dd>
                    </div>
                </dl>
            </article>

            <article class="md:col-span-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 shadow-sm space-y-3">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Personal details') }}</h2>
                <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Name') }}: {{ $user->name }}</p>
                <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Email') }}: {{ $user->email }}</p>
                <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Phone') }}: {{ $user->phone ?? __('Not provided') }}</p>
                <a href="{{ route('frontend.profile.edit') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700">{{ __('Edit profile') }}</a>
            </article>
        </section>

        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Recent orders') }}</h2>
                <a href="{{ route('frontend.orders.index') }}" class="text-sm text-primary-600 hover:text-primary-700">{{ __('View all orders') }}</a>
            </div>
            @if ($orders->isEmpty())
                <p class="text-slate-600 dark:text-slate-300">{{ __('You have not placed any orders yet.') }}</p>
            @else
                <div class="space-y-3">
                    @foreach ($orders as $order)
                        <article class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ __('Order #:number', ['number' => $order->number]) }}</h3>
                                <p class="text-sm text-slate-600 dark:text-slate-300">{{ __('Placed on :date', ['date' => $order->created_at->toFormattedDateString()]) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-slate-500 dark:text-slate-400">{{ ucfirst($order->status) }}</p>
                                <p class="text-base font-semibold text-primary-600">{{ number_format((float) $order->total, 2) }} {{ config('app.currency', 'EUR') }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Saved addresses') }}</h2>
                <a href="{{ route('frontend.profile.addresses') }}" class="text-sm text-primary-600 hover:text-primary-700">{{ __('Manage addresses') }}</a>
            </div>
            @if ($addresses->isEmpty())
                <p class="text-slate-600 dark:text-slate-300">{{ __('You have not added any addresses yet.') }}</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($addresses as $address)
                        <article class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4">
                            <h3 class="font-semibold text-slate-900 dark:text-slate-100">{{ $address->full_name }}</h3>
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $address->full_address }}</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
