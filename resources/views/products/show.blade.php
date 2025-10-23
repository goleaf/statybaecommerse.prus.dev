@extends('frontend.layouts.app')

@section('title', $product->trans('name') ?? $product->name)

@section('content')
    <div class="container mx-auto px-4">
        <nav class="mb-6 text-sm text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-1">
                <li><a href="{{ route('home') }}" class="hover:text-blue-600 dark:hover:text-blue-300">{{ __('Home') }}</a></li>
                <li aria-hidden="true" class="px-1">/</li>
                <li><a href="{{ route('frontend.products.index') }}" class="hover:text-blue-600 dark:hover:text-blue-300">{{ __('Products') }}</a></li>
                @foreach ($product->categories as $category)
                    <li aria-hidden="true" class="px-1">/</li>
                <li><a href="{{ route('frontend.categories.show', $category) }}" class="hover:text-blue-600 dark:hover:text-blue-300">{{ $category->trans('name') ?? $category->name }}</a></li>
                @endforeach
                <li aria-hidden="true" class="px-1">/</li>
                <li class="font-semibold text-gray-900 dark:text-white">{{ $product->trans('name') ?? $product->name }}</li>
            </ol>
        </nav>

        <div class="rounded-2xl bg-white/80 p-4 shadow-sm ring-1 ring-gray-100 dark:bg-slate-900/80 dark:ring-slate-800 lg:p-8">
            @livewire('pages.single-product', ['product' => $product])
        </div>
    </div>
@endsection
