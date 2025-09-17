<div class="mt-8">
    <h2 class="text-lg font-semibold">{{ __('frontend.products.customer_reviews') }}</h2>

    @if ($this->reviews->isEmpty())
        <p class="text-sm text-gray-500 mt-2">{{ __('frontend.products.no_reviews_yet') }}</p>
    @else
        <ul class="mt-4 space-y-4">
            @foreach ($this->reviews as $r)
                <li class="border rounded-md p-3">
                    <div class="flex items-center justify-between">
                        <strong>{{ $r->title }}</strong>
                        <span class="text-xs text-gray-500">{{ $r->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="text-yellow-500 text-sm mt-1">
                        {{ str_repeat('★', (int) $r->rating) }}{{ str_repeat('☆', 5 - (int) $r->rating) }}</div>
                    <p class="mt-2 text-sm text-gray-700">{{ $r->content }}</p>
                </li>
            @endforeach
        </ul>

        @if ($this->reviews->hasPages())
            <div class="mt-6">
                {{ $this->reviews->links('components.pagination') }}
            </div>
        @endif
    @endif
</div>
