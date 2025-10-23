@extends('components.layouts.base')

@section('title', __('Categories'))

@section('content')
    <x-container class="py-8 space-y-6">
        <div>
            <h1 class="text-3xl font-semibold text-gray-900">{{ __('Categories') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('Discover products grouped by category.') }}</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($categories as $category)
                <a href="{{ route('frontend.categories.show', $category) }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm hover:border-primary-300">
                    <h2 class="text-xl font-semibold text-gray-900">{{ $category->name }}</h2>
                    <p class="mt-2 text-sm text-gray-600 line-clamp-3">{{ $category->description }}</p>
                </a>
            @empty
                <div class="col-span-full rounded-lg border border-dashed border-gray-300 p-12 text-center text-gray-500">
                    {{ __('No categories available yet.') }}
                </div>
            @endforelse
        </div>
    </x-container>
@endsection
