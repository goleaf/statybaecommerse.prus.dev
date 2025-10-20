@extends('frontend.layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-10 space-y-6">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Available coupons') }}</h1>
            <p class="text-slate-600 dark:text-slate-300">{{ __('Apply a coupon code below to receive a discount on your order.') }}</p>
        </header>

        <form method="post" action="{{ route('frontend.discounts.apply-coupon') }}" class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300" for="code">{{ __('Coupon code') }}</label>
                <input id="code" name="code" required class="mt-1 w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900">
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700">{{ __('Apply coupon') }}</button>
        </form>

        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Public coupons') }}</h2>
            @if ($codes->isEmpty())
                <p class="text-slate-600 dark:text-slate-300">{{ __('No coupons are currently available.') }}</p>
            @else
                <div class="space-y-3">
                    @foreach ($codes as $code)
                        <article class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $code->code }}</h3>
                                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ $code->description }}</p>
                                </div>
                                <span class="text-sm text-slate-500 dark:text-slate-400">{{ __('Value') }}: {{ $code->value }} {{ $code->type === 'percentage' ? '%' : config('app.currency', 'EUR') }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div>{{ $codes->links() }}</div>
            @endif
        </section>
    </div>
@endsection
