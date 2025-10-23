@extends('frontend.layouts.app')

@section('title', __('Categories'))

@section('content')
    <div class="bg-gray-50 py-12">
        <div class="mx-auto max-w-6xl space-y-10 px-6">
            <header class="flex flex-col gap-2">
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Shop by category') }}</h1>
                <p class="text-sm text-gray-600">{{ __('Choose a product family to view available items in real time.') }}</p>
            </header>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($categories as $category)
                    <a href="{{ route('frontend.categories.show', $category) }}" class="flex flex-col justify-between rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">{{ $category->name }}</h2>
                            @if($category->description)
                                <p class="mt-3 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit(strip_tags($category->description), 120) }}</p>
                            @endif
                        </div>
                        <div class="mt-6 flex items-center justify-between text-xs text-gray-500">
                            <span>{{ trans_choice('{0}No products|{1}1 product|[2,*] :count products', $category->products_count, ['count' => $category->products_count]) }}</span>
                            <span class="font-semibold text-indigo-600">{{ __('Browse') }} →</span>
                        </div>
                    </a>
                @empty
                    <p class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-600">
                        {{ __('Categories will appear once the catalogue is synced.') }}
                    </p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
