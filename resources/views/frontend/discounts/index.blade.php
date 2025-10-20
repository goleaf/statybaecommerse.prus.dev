@extends('frontend.layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-10 space-y-8">
        <header class="space-y-2">
            <h1 class="text-3xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Discounts and promotions') }}</h1>
            <p class="text-slate-600 dark:text-slate-300">{{ __('Take advantage of our current offers to save on your order.') }}</p>
        </header>

        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Active discounts') }}</h2>
            @if ($activeDiscounts->isEmpty())
                <p class="text-slate-600 dark:text-slate-300">{{ __('No active discounts at the moment.') }}</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($activeDiscounts as $discount)
                        <article class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 shadow-sm">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ $discount->name }}</h3>
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $discount->description }}</p>
                            <a href="{{ route('frontend.discounts.show', $discount) }}" class="mt-3 inline-flex items-center text-sm text-primary-600 hover:text-primary-700">{{ __('View details') }}</a>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Upcoming discounts') }}</h2>
            @if ($upcomingDiscounts->isEmpty())
                <p class="text-slate-600 dark:text-slate-300">{{ __('No scheduled discounts yet.') }}</p>
            @else
                <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-300">
                    @foreach ($upcomingDiscounts as $discount)
                        <li>
                            <strong class="text-slate-900 dark:text-slate-100">{{ $discount->name }}</strong>
                            — {{ __('Starts on :date', ['date' => optional($discount->starts_at)->toFormattedDateString()]) }}
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="space-y-4">
            <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ __('Recently expired') }}</h2>
            @if ($expiredDiscounts->isEmpty())
                <p class="text-slate-600 dark:text-slate-300">{{ __('No expired discounts to show.') }}</p>
            @else
                <ul class="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                    @foreach ($expiredDiscounts as $discount)
                        <li>{{ $discount->name }} — {{ __('Ended on :date', ['date' => optional($discount->ends_at)->toFormattedDateString()]) }}</li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div>
            <a href="{{ route('frontend.discounts.coupons') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700">{{ __('Browse available coupons') }}</a>
        </div>
    </div>
@endsection
