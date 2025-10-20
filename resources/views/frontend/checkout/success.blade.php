@extends('frontend.layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-12 space-y-8 text-center">
        <div class="space-y-3">
            <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Thank you for your order!') }}</h1>
            <p class="text-slate-600 dark:text-slate-300">{{ __('We have received your order and sent a confirmation email with the details.') }}</p>
        </div>

        <section class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 space-y-4 text-left">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Order details') }}</h2>
            <dl class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                <div class="flex justify-between">
                    <dt>{{ __('Order number') }}</dt>
                    <dd>{{ $checkout['order_number'] }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt>{{ __('Customer') }}</dt>
                    <dd>{{ $checkout['customer']['name'] }} ({{ $checkout['customer']['email'] }})</dd>
                </div>
                <div class="flex justify-between">
                    <dt>{{ __('Total paid') }}</dt>
                    <dd>{{ number_format($checkout['summary']['total'], 2) }} {{ config('app.currency', 'EUR') }}</dd>
                </div>
            </dl>

            <div class="border-t border-slate-200 dark:border-slate-700 pt-4">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('Items') }}</h3>
                <ul class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                    @foreach ($checkout['items'] as $item)
                        <li class="flex justify-between">
                            <span>{{ $item['name'] }} × {{ $item['quantity'] }}</span>
                            <span>{{ number_format((float) $item['total'], 2) }} {{ config('app.currency', 'EUR') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>

        <div class="space-x-3">
            <a href="{{ route('frontend.products.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700">{{ __('Continue shopping') }}</a>
            <a href="{{ route('frontend.profile.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">{{ __('View your profile') }}</a>
        </div>
    </div>
@endsection
