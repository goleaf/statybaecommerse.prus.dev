<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-gray-900">
                {{ __('translations.customer_reviews') }}
            </h3>

            @auth
                <button
                        wire:click="toggleReviewForm"
                        wire:confirm="{{ __('translations.confirm_toggle_review_form') }}"
                        class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    {{ __('translations.write_review') }}
                </button>
            @else
                <a
                   href="{{ route('login') }}"
                   class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                    {{ __('translations.login_to_review') }}
                </a>
            @endauth
        </div>

        @if ($totalReviews > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-6">
                <!-- Average Rating -->
                <div class="flex items-center">
                    <div class="text-center">
                        <div class="text-4xl font-bold text-gray-900 mb-2">{{ $averageRating }}</div>
                        <div class="flex items-center justify-center mb-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-5 h-5 {{ $i <= $averageRating ? 'text-yellow-400' : 'text-gray-300' }}"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                          d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ trans_choice('translations.reviews_count', $totalReviews, ['count' => $totalReviews]) }}
                        </div>
                    </div>
                </div>

                <!-- Rating Distribution -->
                <div class="space-y-2">
                    @for ($rating = 5; $rating >= 1; $rating--)
                        @php
                            $count = $ratingDistribution[$rating] ?? 0;
                            $percentage = $totalReviews > 0 ? ($count / $totalReviews) * 100 : 0;
                        @endphp
                        <div class="flex items-center text-sm">
                            <span class="w-8 text-gray-600">{{ $rating }}★</span>
                            <div class="flex-1 mx-3 bg-gray-200 rounded-full h-2">
                                <div class="bg-yellow-400 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                            </div>
                            <span class="w-8 text-right text-gray-600">{{ $count }}</span>
                        </div>
                    @endfor
                </div>
            </div>
        @endif
    </div>

    <!-- Review Form -->
    @if ($showReviewForm)
        <div class="p-6 border-b border-gray-200 bg-gray-50">
            <h4 class="text-lg font-medium text-gray-900 mb-4">{{ __('translations.write_your_review') }}</h4>

            <form wire:submit="submitReview" class="space-y-4">
                <!-- Rating -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('translations.rating') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center space-x-1">
                        @for ($i = 1; $i <= 5; $i++)
                            <button
                                    type="button"
                                    wire:click="$set('rating', {{ $i }})"
                                    class="text-2xl {{ $i <= $rating ? 'text-yellow-400' : 'text-gray-300' }} hover:text-yellow-400 transition-colors">
                                ★
                            </button>
                        @endfor
                        <span class="ml-2 text-sm text-gray-600">({{ $rating }}/5)</span>
                    </div>
                    @error('rating')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('translations.review_title') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                           wire:model="title"
                           type="text"
                           id="title"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                           placeholder="{{ __('translations.review_title_placeholder') }}">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Content -->
                <div>
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('translations.review_content') }} <span class="text-red-500">*</span>
                    </label>
                    <textarea
                              wire:model="content"
                              id="content"
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                              placeholder="{{ __('translations.review_content_placeholder') }}"></textarea>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if ($errors->has('review'))
                    <div class="p-3 bg-red-100 border border-red-300 text-red-700 rounded-md">
                        {{ $errors->first('review') }}
                    </div>
                @endif

                <div class="flex items-center space-x-3">
                    <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                        {{ __('translations.submit_review') }}
                    </button>
                    <button
                            type="button"
                            wire:click="toggleReviewForm"
                            wire:confirm="{{ __('translations.confirm_toggle_review_form') }}"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-md transition-colors duration-200">
                        {{ __('translations.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Reviews List -->
    <div class="divide-y divide-gray-200">
        @forelse($reviews as $review)
            <div class="p-6">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-primary-600">
                                    {{ substr($review->user->name, 0, 1) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ $review->user->name }}</p>
                            <div class="flex items-center mt-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                                <span class="ml-2 text-xs text-gray-500">
                                    {{ $review->created_at->format('M j, Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="text-lg font-medium text-gray-900 mb-2">{{ $review->title }}</h4>
                <p class="text-gray-700 leading-relaxed">{{ $review->content }}</p>
            </div>
        @empty
            <div class="p-6 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('translations.no_reviews_yet') }}</h3>
                <p class="text-gray-600">{{ __('translations.be_first_to_review') }}</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($reviews->hasPages())
        <div class="p-6 border-t border-gray-200">
            {{ $reviews->links('components.pagination') }}
        </div>
    @endif
</div>
