@extends('components.layouts.base')

@section('title', '#'.$tag->name.' | '. __('messages.news))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <a href="{{ route('news.index') }}" class="text-sm text-blue-600 hover:text-blue-500">&larr; {{ __('messages.news) }}</a>
        <h1 class="text-4xl font-bold text-gray-900 mt-2 mb-3">#{{ $tag->name }}</h1>
        @if($tag->description)
        <p class="text-lg text-gray-600">{{ $tag->description }}</p>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-3 space-y-8">
            @if($news->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @include('news.partials.grid-items', ['newsItems' => $news])
            </div>

            <div class="mt-6">
                <x-perfect-pagination :paginator="$news->appends(request()->query())" />
            </div>
            @else
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <h2 class="text-xl font-semibold text-gray-900">{{ __('messages.news) }}</h2>
                <p class="text-gray-600">{{ __('messages.news) }}</p>
            </div>
            @endif
        </div>

        <aside class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.news) }}</h2>
                <ul class="space-y-2">
                    @foreach($categories as $item)
                    <li>
                        <a href="{{ route('news.category', $item->slug) }}"
                           class="block px-3 py-2 rounded-md text-sm text-gray-700 hover:bg-gray-100">
                            {{ $item->name }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.news) }}</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($tags as $item)
                    <a href="{{ route('news.tag', $item->slug) }}"
                       class="px-3 py-1 rounded-full text-sm {{ $item->id === $tag->id ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        #{{ $item->name }}
                    </a>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
