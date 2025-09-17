@extends('layouts.app')

@section('title', $review->title ?: __('reviews_review'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-2">
                        <div class="flex text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-6 h-6 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <span class="text-lg font-semibold text-gray-700">{{ $review->rating }}/5</span>
                    </div>
                    @if($review->is_featured)
                        <span class="bg-yellow-100 text-yellow-800 text-sm font-medium px-3 py-1 rounded-full">
                            {{ __('reviews_featured') }}
                        </span>
                    @endif
                </div>

                @if($review->title)
                    <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $review->title }}</h1>
                @endif

                <div class="flex items-center space-x-4 text-sm text-gray-500 mb-6">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">{{ $review->reviewer_name }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <time datetime="{{ $review->created_at->toISOString() }}">
                            {{ $review->created_at->format('F j, Y') }}
                        </time>
                    </div>
                    @if($review->product)
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <a href="{{ route('products.show', $review->product) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                {{ $review->product->name }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            @if($review->comment)
                <div class="prose max-w-none">
                    <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $review->comment }}</p>
                </div>
            @endif

            @if($review->metadata && count($review->metadata) > 0)
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('reviews_additional_info') }}</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2">
                        @foreach($review->metadata as $key => $value)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $key)) }}</dt>
                                <dd class="text-sm text-gray-900">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif

            <div class="mt-8 pt-6 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <a href="{{ route('reviews.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                        ← {{ __('reviews_back_to_reviews') }}
                    </a>
                    
                    @auth
                        @if(Auth::id() === $review->user_id)
                            <div class="flex space-x-4">
                                <a href="{{ route('reviews.edit', $review) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                                    {{ __('reviews_edit') }}
                                </a>
                                <form method="POST" action="{{ route('reviews.destroy', $review) }}" class="inline" onsubmit="return confirm('{{ __('reviews_confirm_delete') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                                        {{ __('reviews_delete') }}
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

