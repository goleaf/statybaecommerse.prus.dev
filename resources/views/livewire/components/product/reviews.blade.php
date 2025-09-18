<div class="mt-8">
    <h2 class="text-lg font-semibold">{{ __('frontend.products.customer_reviews') }}</h2>

    @if ($reviews->isEmpty())
        <p class="text-sm text-gray-500 mt-2">{{ __('frontend.products.no_reviews_yet') }}</p>
    @else
        <ul class="mt-4 space-y-4">
            @foreach ($reviews as $review)
                <li class="border rounded-md p-3">
                    <div class="flex items-center justify-between">
                        <strong>{{ $review->title ?? 'Review' }}</strong>
                        <span class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="text-yellow-500 text-sm mt-1">
                        {{ str_repeat('★', (int) $review->rating) }}{{ str_repeat('☆', 5 - (int) $review->rating) }}
                    </div>
                    <p class="mt-2 text-sm text-gray-700">{{ $review->content }}</p>
                    @if($review->reviewer_name)
                        <p class="text-xs text-gray-500 mt-2">- {{ $review->reviewer_name }}</p>
                    @endif
                </li>
            @endforeach
        </ul>

        @if ($reviews->hasPages())
            <div class="mt-6">
                {{ $reviews->links('components.pagination') }}
            </div>
        @endif
    @endif
</div>
