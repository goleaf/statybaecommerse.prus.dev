@extends('frontend.layouts.app')

@section('title', __('Categories'))

@section('content')
    <div class="container mx-auto px-4">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Product categories') }}</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                {{ __('Browse the full catalogue of categories to find exactly what you need for your next project.') }}
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            @forelse ($categories as $category)
                <section class="rounded-2xl bg-white/80 p-6 shadow-sm ring-1 ring-gray-100 dark:bg-slate-900/70 dark:ring-slate-800">
                    <header class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                                <a href="{{ route('frontend.categories.show', $category) }}" class="hover:text-blue-600 dark:hover:text-blue-300">
                                    {{ $category->name }}
                                </a>
                            </h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                {{ \Illuminate\Support\Str::limit(strip_tags($category->description ?? ''), 120) }}
                            </p>
                        </div>
                        <a href="{{ route('frontend.categories.show', $category) }}" class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 dark:hover:bg-blue-900/50">
                            {{ __('View') }}
                        </a>
                    </header>
                    @if ($category->children->isNotEmpty())
                        <ul class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                            @foreach ($category->children as $child)
                                <li>
                                    <a href="{{ route('frontend.categories.show', $child) }}" class="hover:text-blue-600 dark:hover:text-blue-300">
                                        {{ $child->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('This category contains carefully selected tools and accessories.') }}
                        </p>
                    @endif
                </section>
            @empty
                <p class="col-span-full rounded-lg bg-white/80 p-6 text-center text-sm text-gray-600 ring-1 ring-gray-100 dark:bg-slate-900/60 dark:text-gray-300 dark:ring-slate-800">
                    {{ __('Categories will appear once the catalogue is published. Please check back soon!') }}
                </p>
            @endforelse
        </div>
    </div>
@endsection
