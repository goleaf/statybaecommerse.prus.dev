@extends('frontend.layouts.app')

@section('title', __('Brands'))

@section('content')
    <div class="bg-white py-12">
        <div class="mx-auto max-w-7xl space-y-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl space-y-4">
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700">
                    <x-untitledui-flag-06 class="h-4 w-4" />
                    {{ __('Trusted partners') }}
                </span>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">{{ __('Brands we work with') }}</h1>
                <p class="text-gray-600">{{ __('Meet the manufacturers providing resilient building materials, professional power tools, and protective equipment across Lithuania and the Baltics.') }}</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($brands as $brand)
                    <article class="flex h-full flex-col justify-between rounded-3xl border border-gray-200 bg-gray-50 p-6 shadow-sm transition hover:-translate-y-1 hover:border-emerald-400 hover:bg-white">
                        <div class="space-y-3">
                            <div class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600">
                                <x-untitledui-badge-check class="h-4 w-4" />
                                {{ $brand->name }}
                            </div>
                            <p class="text-sm text-gray-600">{{ \Illuminate\Support\Str::limit(strip_tags($brand->description ?? ''), 140) }}</p>
                            <p class="text-xs uppercase tracking-wide text-gray-400">{{ __('Active products: :count', ['count' => $brand->visible_products_count ?? $brand->products_count ?? 0]) }}</p>
                        </div>
                        <div class="mt-6 flex items-center justify-between">
                            <a href="{{ route('frontend.brands.show', $brand) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 hover:text-emerald-700">
                                {{ __('View collection') }}
                                <x-untitledui-arrow-up-right class="h-4 w-4" />
                            </a>
                            @if($brand->website)
                                <a href="{{ $brand->website }}" class="text-xs font-semibold text-gray-400 hover:text-emerald-600" target="_blank" rel="noopener">
                                    {{ parse_url($brand->website, PHP_URL_HOST) ?? $brand->website }}
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
@endsection

