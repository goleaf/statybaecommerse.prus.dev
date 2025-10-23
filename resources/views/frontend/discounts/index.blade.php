@extends('components.layouts.base')

@section('title', __('Discounts'))

@section('content')
    <x-container class="py-8 space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-gray-900">{{ __('Discounts') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('Active promotions you can use during checkout.') }}</p>
        </div>

        <div class="space-y-4">
            @forelse ($discounts as $discount)
                <article class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">{{ $discount->name }}</h2>
                        <span class="text-sm text-gray-500">{{ ucfirst($discount->type) }}</span>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">{{ $discount->description }}</p>
                    <div class="mt-4 text-sm text-gray-500">
                        @if ($discount->starts_at)
                            <p>{{ __('Starts at :date', ['date' => $discount->starts_at->format('Y-m-d')]) }}</p>
                        @endif
                        @if ($discount->ends_at)
                            <p>{{ __('Ends at :date', ['date' => $discount->ends_at->format('Y-m-d')]) }}</p>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-dashed border-gray-300 p-12 text-center text-gray-500">
                    {{ __('No discounts are currently available.') }}
                </div>
            @endforelse
        </div>
    </x-container>
@endsection
