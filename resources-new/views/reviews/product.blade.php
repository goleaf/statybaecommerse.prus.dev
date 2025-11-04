@extends('components.layouts.base')

@section('title', __('reviews_reviews_for') . ' ' . $product->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="mb-8">
            <div class="flex items-center space-x-2 text-sm text-gray-500 mb-4">
                <a href="{{ route('products.show', $product) }}" class="hover:text-gray-700">{{ $product->name }}</a>
                <span>›</span>
                <span>{{ __('reviews_reviews') }}</span>
            </div>
            
            <h1 class="text-3xl font-bold text-gray-900 mb-4">
                {{ __('reviews_reviews_for') }} {{ $product->name }}
            </h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Rating Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('reviews_rating_summary') }}</h2>
                    
                    <div class="text-center mb-6">
                        <div class="text-4xl font-bold text-gray-900 mb-2">{{ number_format($ratingStats['average'], 1) }}</div>
                        <div class="flex justify-center text-yellow-400 mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-6 h-6 {{ $i <= round($ratingStats['average']) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <p class="text-gray-600">{{ $ratingStats['count'] }} {{ __('reviews_review_count', $ratingStats['count']) }}</p>
                    </div>

                    <!-- Rating Distribution -->
                    <div class="space-y-2">
                        @for($i = 5; $i >= 1; $i--)
                            @php
                                $count = $ratingStats['distribution'][$i] ?? 0;
                                $percentage = $ratingStats['count'] > 0 ? ($count / $ratingStats['count']) * 100 : 0;
                            @endphp
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-600 w-8">{{ $i }}</span>
                                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-400 h-2 rounded-full w-var" data-width="{{ $percentage }}"></div>
                                </div>
                                <span class="text-sm text-gray-600 w-8">{{ $count }}</span>
                            </div>
                        @endfor
                    </div>

                    <div class="mt-6">
                        <a href="{{ route('reviews.create', ['product_id' => $product->id]) }}" 
                           class="w-full bg-blue-600 text-white text-center py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium block">
                            {{ __('reviews_write_review') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Reviews List -->
            <div class="lg:col-span-2">
                @if($reviews->count() > 0)
                    <div class="space-y-6">
                        @foreach($reviews as $review)
                            <div class="bg-white rounded-lg shadow-md p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ substr($review->reviewer_name, 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-900">{{ $review->reviewer_name }}</h3>
                                            <div class="flex items-center space-x-1">
                                                <div class="flex text-yellow-400">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                    @endfor
                                                </div>
                                                <span class="text-sm text-gray-500">{{ $review->rating }}/5</span>
                                            </div>
                                        </div>
                                    </div>
                                    @if($review->is_featured)
                                        <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                            {{ __('reviews_featured') }}
                                        </span>
                                    @endif
                                </div>

                                @if($review->title)
                                    <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ $review->title }}</h4>
                                @endif

                                @if($review->comment)
                                    <p class="text-gray-700 mb-4 whitespace-pre-wrap">{{ $review->comment }}</p>
                                @endif

                                <div class="flex items-center justify-between text-sm text-gray-500">
                                    <time datetime="{{ $review->created_at->toISOString() }}">
                                        {{ $review->created_at->format('F j, Y') }}
                                    </time>
                                    <a href="{{ route('reviews.show', $review) }}" class="text-blue-600 hover:text-blue-800">
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
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('reviews_no_reviews_for_product') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('reviews_be_first_to_review') }}</p>
                        <div class="mt-6">
                            <a href="{{ route('reviews.create', ['product_id' => $product->id]) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ __('reviews_write_review') }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

