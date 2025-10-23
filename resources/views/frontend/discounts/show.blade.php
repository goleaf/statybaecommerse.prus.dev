@extends('frontend.layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto px-4 py-10 space-y-6">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ $discount->name }}</h1>
            <p class="text-slate-600 dark:text-slate-300">{{ $discount->description }}</p>
        </header>

        <section class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-6 space-y-3">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Offer details') }}</h2>
            <ul class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                <li>{{ __('Type') }}: {{ $discount->type }}</li>
                <li>{{ __('Value') }}: {{ $discount->value }}</li>
                <li>{{ __('Active from') }}: {{ optional($discount->starts_at)->toFormattedDateString() ?? __('Immediately') }}</li>
                <li>{{ __('Expires on') }}: {{ optional($discount->ends_at)->toFormattedDateString() ?? __('No expiry') }}</li>
            </ul>
        </section>

        @if ($codes->isNotEmpty())
            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Coupon codes') }}</h2>
                <div class="space-y-3">
                    @foreach ($codes as $code)
                        <article class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4">
                            <div class="flex items-center justify-between">
                                <strong class="text-lg text-slate-900 dark:text-slate-100">{{ $code->code }}</strong>
                                <span class="text-sm text-slate-500 dark:text-slate-400">{{ __('Value') }}: {{ $code->value }} {{ $code->type === 'percentage' ? '%' : config('app.currency', 'EUR') }}</span>
                            </div>
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $code->description }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <a href="{{ route('frontend.discounts.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700">{{ __('Back to discounts') }}</a>
    </div>
@endsection
