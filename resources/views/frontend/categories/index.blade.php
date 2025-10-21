@extends('frontend.layouts.app')

@section('title', __('Product categories'))

@section('content')
    <div class="bg-white py-12">
        <div class="mx-auto max-w-7xl space-y-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl space-y-4">
                <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-700">
                    <x-untitledui-folder class="h-4 w-4" />
                    {{ __('Explore by category') }}
                </span>
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">{{ __('Construction catalogue categories') }}</h1>
                <p class="text-gray-600">{{ __('Navigate the full assortment of tools, materials, and safety equipment curated for Baltic building professionals.') }}</p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($categories as $category)
                    <article class="flex flex-col justify-between rounded-3xl border border-gray-200 bg-gray-50 p-6 shadow-sm transition hover:-translate-y-1 hover:border-blue-400 hover:bg-white">
                        <div class="space-y-3">
                            <div class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600">
                                <x-untitledui-collection class="h-4 w-4" />
                                {{ $category->name }}
                            </div>
                            <p class="text-sm text-gray-600">{{ \Illuminate\Support\Str::limit(strip_tags($category->description ?? ''), 120) }}</p>
                        </div>
                        @if($category->children->isNotEmpty())
                            <ul class="mt-4 space-y-2 text-sm text-gray-500">
                                @foreach($category->children->take(4) as $child)
                                    <li class="flex items-center gap-2">
                                        <x-untitledui-arrow-right class="h-3.5 w-3.5 text-blue-500" />
                                        <span>{{ $child->name }}</span>
                                    </li>
                                @endforeach
                                @if($category->children->count() > 4)
                                    <li class="text-xs text-gray-400">{{ __('+:count more subcategories', ['count' => $category->children->count() - 4]) }}</li>
                                @endif
                            </ul>
                        @endif
                        <a href="{{ route('frontend.categories.show', $category) }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700">
                            {{ __('Browse category') }}
                            <x-untitledui-arrow-up-right class="h-4 w-4" />
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
@endsection

