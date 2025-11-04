@extends('components.layouts.base')

@section('title', __('reviews_reviews'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('reviews_reviews') }}</h1>
            <p class="text-gray-600">{{ __('reviews_index_description') }}</p>
        </div>

        @if($reviews->count() > 0)
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach($reviews as $review)
                    <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2">
                                <div class="flex text-yellow-400">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-5 h-5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <span class="text-sm text-gray-500">{{ $review->rating }}/5</span>
                            </div>
                            @if($review->is_featured)
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    {{ __('reviews_featured') }}
                                </span>
                            @endif
                        </div>

                        @if($review->title)
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $review->title }}</h3>
                        @endif

                        @if($review->comment)
                            <p class="text-gray-600 mb-4 line-clamp-3">{{ $review->comment }}</p>
                        @endif

                        <div class="flex items-center justify-between text-sm text-gray-500">
                            <div>
                                <span class="font-medium">{{ $review->reviewer_name }}</span>
                                @if($review->product)
                                    <span class="mx-1">•</span>
                                    <a href="{{ route('products.show', $review->product) }}" class="text-blue-600 hover:text-blue-800">
                                        {{ $review->product->name }}
                                    </a>
                                @endif
                            </div>
                            <time datetime="{{ $review->created_at->toISOString() }}">
                                {{ $review->created_at->format('M j, Y') }}
                            </time>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('reviews.show', $review) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                {{ __('reviews_read_more') }}
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $reviews->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('reviews_no_reviews') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('reviews_no_reviews_description') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

