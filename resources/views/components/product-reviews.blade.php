@props([
    'product' => null,
    'reviews' => null,
    'title' => null,
    'subtitle' => null,
    'showRating' => true,
    'showReviewForm' => true,
    'showHelpfulVotes' => true,
    'maxReviews' => null,
    'sortBy' => 'newest', // newest, oldest, highest, lowest, helpful
])

@php
    $product = $product ?? new \App\Models\Product();
    $title = $title ?? __('Customer Reviews');
    $subtitle = $subtitle ?? __('Read what our customers say about this product');

    // Get reviews from database or use provided reviews
    if (!$reviews) {
        $query = $product->reviews()->with(['user', 'product']);

        // Apply sorting
        switch ($sortBy) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'highest':
                $query->orderBy('rating', 'desc');
                break;
            case 'lowest':
                $query->orderBy('rating', 'asc');
                break;
            case 'helpful':
                $query->orderBy('helpful_votes', 'desc');
                break;
        }

        $reviews = $query->get();
    }

    if ($maxReviews) {
        $reviews = $reviews->take($maxReviews);
    }

    // Calculate rating statistics
    $totalReviews = $reviews->count();
    $averageRating = $totalReviews > 0 ? $reviews->avg('rating') : 0;
    $ratingDistribution = [];
    for ($i = 5; $i >= 1; $i--) {
        $ratingDistribution[$i] = $reviews->where('rating', $i)->count();
    }
@endphp

<div class="product-reviews" x-data="productReviews()">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">{{ $title }}</h2>
            <p class="text-lg text-gray-600">{{ $subtitle }}</p>
        </div>

        @if ($totalReviews > 0)
            {{-- Rating Summary --}}
            @if ($showRating)
                <div class="bg-white border border-gray-200 rounded-2xl p-8 mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Overall Rating --}}
                        <div class="text-center">
                            <div class="text-6xl font-bold text-gray-900 mb-2">{{ number_format($averageRating, 1) }}
                            </div>
                            <div class="flex items-center justify-center gap-1 mb-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-8 h-8 {{ $i <= $averageRating ? 'text-yellow-400' : 'text-gray-300' }}"
                                         fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                            </div>
                            <p class="text-gray-600">{{ $totalReviews }} {{ __('reviews') }}</p>
                        </div>

                        {{-- Rating Distribution --}}
                        <div class="space-y-3">
                            @for ($i = 5; $i >= 1; $i--)
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-medium text-gray-700 w-8">{{ $i }}</span>
                                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                                        <div class="bg-yellow-400 h-2 rounded-full"
                                             style="width: {{ $totalReviews > 0 ? ($ratingDistribution[$i] / $totalReviews) * 100 : 0 }}%">
                                        </div>
                                    </div>
                                    <span class="text-sm text-gray-600 w-8">{{ $ratingDistribution[$i] }}</span>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            @endif

            {{-- Sort and Filter --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">{{ __('Sort by') }}:</span>
                    <select x-model="sortBy" @change="applySorting()"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="newest">{{ __('Newest First') }}</option>
                        <option value="oldest">{{ __('Oldest First') }}</option>
                        <option value="highest">{{ __('Highest Rating') }}</option>
                        <option value="lowest">{{ __('Lowest Rating') }}</option>
                        <option value="helpful">{{ __('Most Helpful') }}</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">{{ __('Filter by rating') }}:</span>
                    <div class="flex gap-1">
                        @for ($i = 5; $i >= 1; $i--)
                            <button @click="filterByRating({{ $i }})"
                                    :class="ratingFilter === {{ $i }} ? 'text-yellow-400' :
                                        'text-gray-300 hover:text-yellow-400'"
                                    class="transition-colors duration-200">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                          d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </button>
                        @endfor
                    </div>
                </div>
            </div>

            {{-- Reviews List --}}
            <div class="space-y-6">
                @foreach ($reviews as $review)
                    <div
                         class="bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-medium transition-shadow duration-200">
                        {{-- Review Header --}}
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-4">
                                {{-- User Avatar --}}
                                <div
                                     class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                    @if ($review->user && $review->user->avatar)
                                        <img src="{{ $review->user->avatar }}"
                                             alt="{{ $review->user->name }}"
                                             class="w-12 h-12 rounded-full object-cover">
                                    @else
                                        <span class="text-white font-semibold text-lg">
                                            {{ substr($review->user->name ?? 'A', 0, 1) }}
                                        </span>
                                    @endif
                                </div>

                                {{-- User Info --}}
                                <div>
                                    <h4 class="font-semibold text-gray-900">
                                        {{ $review->user->name ?? __('Anonymous') }}</h4>
                                    <div class="flex items-center gap-2">
                                        <div class="flex items-center">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                                     fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                          d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @endfor
                                        </div>
                                        <span
                                              class="text-sm text-gray-600">{{ $review->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Verified Badge --}}
                            @if ($review->verified_purchase ?? false)
                                <span class="inline-flex items-center gap-1 text-xs text-green-600 font-medium">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                              d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                              clip-rule="evenodd" />
                                    </svg>
                                    {{ __('Verified Purchase') }}
                                </span>
                            @endif
                        </div>

                        {{-- Review Content --}}
                        <div class="mb-4">
                            <h5 class="font-semibold text-gray-900 mb-2">{{ $review->title }}</h5>
                            <p class="text-gray-700 leading-relaxed">{{ $review->content }}</p>
                        </div>

                        {{-- Review Images --}}
                        @if ($review->images && $review->images->count() > 0)
                            <div class="mb-4">
                                <div class="flex gap-2 overflow-x-auto">
                                    @foreach ($review->images as $image)
                                        <img src="{{ $image->url }}"
                                             alt="{{ $review->title }}"
                                             class="w-20 h-20 object-cover rounded-lg flex-shrink-0 cursor-pointer hover:opacity-75 transition-opacity duration-200"
                                             @click="openImageModal('{{ $image->url }}')">
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Helpful Votes --}}
                        @if ($showHelpfulVotes)
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <div class="flex items-center gap-4">
                                    <span class="text-sm text-gray-600">
                                        {{ __('Was this review helpful?') }}
                                    </span>
                                    <div class="flex gap-2">
                                        <button @click="voteHelpful({{ $review->id }}, true)"
                                                class="flex items-center gap-1 text-sm text-green-600 hover:text-green-700 font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V18m-7-8a2 2 0 01-2-2V5a2 2 0 012-2h2.343M11 7.16l-2.343 2.343a2 2 0 00-.586 1.414V18">
                                                </path>
                                            </svg>
                                            {{ __('Yes') }} ({{ $review->helpful_votes ?? 0 }})
                                        </button>
                                        <button @click="voteHelpful({{ $review->id }}, false)"
                                                class="flex items-center gap-1 text-sm text-red-600 hover:text-red-700 font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.737 3h4.018c.163 0 .326.02.485.06L17 4m-7 10V6m7 8a2 2 0 012-2V5a2 2 0 00-2-2h-2.343M13 16.84l2.343-2.343a2 2 0 00.586-1.414V6">
                                                </path>
                                            </svg>
                                            {{ __('No') }} ({{ $review->unhelpful_votes ?? 0 }})
                                        </button>
                                    </div>
                                </div>

                                <button class="text-sm text-gray-600 hover:text-gray-700 font-medium">
                                    {{ __('Report') }}
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Load More Button --}}
            @if ($reviews->count() >= 10)
                <div class="text-center mt-8">
                    <button class="btn-gradient px-8 py-3 rounded-xl font-semibold">
                        {{ __('Load More Reviews') }}
                    </button>
                </div>
            @endif
        @else
            {{-- No Reviews --}}
            <div class="text-center py-16">
                <svg class="w-24 h-24 text-gray-300 mx-auto mb-6" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                    </path>
                </svg>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ __('No reviews yet') }}</h3>
                <p class="text-gray-600 mb-8">{{ __('Be the first to review this product!') }}</p>
            </div>
        @endif

        {{-- Review Form --}}
        @if ($showReviewForm)
            <div class="mt-12 bg-white border border-gray-200 rounded-2xl p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Write a Review') }}</h3>

                <form @submit.prevent="submitReview()" class="space-y-6">
                    {{-- Rating --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Rating') }} <span
                                  class="text-red-500">*</span></label>
                        <div class="flex gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" @click="reviewForm.rating = {{ $i }}"
                                        :class="reviewForm.rating >= {{ $i }} ? 'text-yellow-400' : 'text-gray-300'"
                                        class="transition-colors duration-200">
                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                              d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </button>
                            @endfor
                        </div>
                    </div>

                    {{-- Title --}}
                    <div>
                        <label for="review-title"
                               class="block text-sm font-medium text-gray-700 mb-2">{{ __('Review Title') }} <span
                                  class="text-red-500">*</span></label>
                        <input type="text" id="review-title" x-model="reviewForm.title" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder:text-gray-500">
                    </div>

                    {{-- Content --}}
                    <div>
                        <label for="review-content"
                               class="block text-sm font-medium text-gray-700 mb-2">{{ __('Your Review') }} <span
                                  class="text-red-500">*</span></label>
                        <textarea id="review-content" x-model="reviewForm.content" rows="6" required
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-gray-900 placeholder:text-gray-500"
                                  placeholder="{{ __('Share your experience with this product...') }}"></textarea>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" :disabled="reviewForm.loading"
                            class="btn-gradient px-8 py-3 rounded-xl font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!reviewForm.loading">{{ __('Submit Review') }}</span>
                        <span x-show="reviewForm.loading" class="flex items-center gap-2">
                            <svg class="animate-spin w-5 h-5" fill="none" stroke="currentColor"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                            {{ __('Submitting...') }}
                        </span>
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>

<script>
    function productReviews() {
        return {
            sortBy: '{{ $sortBy }}',
            ratingFilter: null,
            reviewForm: {
                rating: 0,
                title: '',
                content: '',
                loading: false
            },

            applySorting() {
                const url = new URL(window.location);
                url.searchParams.set('sort', this.sortBy);
                window.location.href = url.toString();
            },

            filterByRating(rating) {
                this.ratingFilter = this.ratingFilter === rating ? null : rating;
                // Apply filter logic
            },

            voteHelpful(reviewId, helpful) {
                // Vote helpful/unhelpful logic
                fetch('/reviews/vote', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        review_id: reviewId,
                        helpful: helpful
                    })
                });
            },

            async submitReview() {
                if (this.reviewForm.rating === 0) {
                    alert('{{ __('Please select a rating.') }}');
                    return;
                }

                this.reviewForm.loading = true;

                try {
                    const response = await fetch('/reviews', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            product_id: {{ $product->id ?? 0 }},
                            rating: this.reviewForm.rating,
                            title: this.reviewForm.title,
                            content: this.reviewForm.content
                        })
                    });

                    if (response.ok) {
                        this.reviewForm = {
                            rating: 0,
                            title: '',
                            content: '',
                            loading: false
                        };
                        alert('{{ __('Review submitted successfully!') }}');
                        window.location.reload();
                    } else {
                        alert('{{ __('Failed to submit review. Please try again.') }}');
                    }
                } catch (error) {
                    alert('{{ __('Network error. Please check your connection and try again.') }}');
                } finally {
                    this.reviewForm.loading = false;
                }
            },

            openImageModal(imageUrl) {
                // Open image in modal
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-black z-50 flex items-center justify-center';
                modal.innerHTML = `
                <div class="relative max-w-4xl max-h-full p-4">
                    <button onclick="this.parentElement.parentElement.remove()" class="absolute top-4 right-4 w-10 h-10 bg-white/90 rounded-full flex items-center justify-center text-gray-700 hover:bg-white transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <img src="${imageUrl}" alt="Review Image" class="max-w-full max-h-full object-contain">
                </div>
            `;
                document.body.appendChild(modal);
            }
        }
    }
</script>
