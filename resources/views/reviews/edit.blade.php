@extends('layouts.app')

@section('title', __('reviews_edit_review'))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('reviews_edit_review') }}</h1>
                @if($review->product)
                    <p class="text-gray-600">{{ __('reviews_edit_review_for') }} <strong>{{ $review->product->name }}</strong></p>
                @endif
            </div>

            <form method="POST" action="{{ route('reviews.update', $review) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="rating" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('reviews_rating') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="flex space-x-1" id="rating-container">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" class="rating-star text-2xl text-gray-300 hover:text-yellow-400 focus:outline-none focus:text-yellow-400" data-rating="{{ $i }}">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </button>
                        @endfor
                    </div>
                    <input type="hidden" name="rating" id="rating" value="{{ old('rating', $review->rating) }}" required>
                    @error('rating')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('reviews_title') }}
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title', $review->title) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('title') border-red-500 @enderror"
                           placeholder="{{ __('reviews_title_placeholder') }}">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('reviews_comment') }}
                    </label>
                    <textarea name="comment" id="comment" rows="6" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('comment') border-red-500 @enderror"
                              placeholder="{{ __('reviews_comment_placeholder') }}">{{ old('comment', $review->comment) }}</textarea>
                    @error('comment')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                {{ __('reviews_edit_notice') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <a href="{{ route('reviews.show', $review) }}" 
                       class="text-gray-600 hover:text-gray-800 font-medium">
                        {{ __('reviews_cancel') }}
                    </a>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-medium">
                        {{ __('reviews_update_review') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.rating-star');
    const ratingInput = document.getElementById('rating');
    const currentRating = parseInt(ratingInput.value);

    // Set initial rating
    updateStars(currentRating);

    stars.forEach((star, index) => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            ratingInput.value = rating;
            updateStars(rating);
        });

        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            updateStars(rating);
        });
    });

    document.getElementById('rating-container').addEventListener('mouseleave', function() {
        const currentRating = parseInt(ratingInput.value);
        updateStars(currentRating);
    });

    function updateStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            } else {
                star.classList.remove('text-yellow-400');
                star.classList.add('text-gray-300');
            }
        });
    }
});
</script>
@endsection


