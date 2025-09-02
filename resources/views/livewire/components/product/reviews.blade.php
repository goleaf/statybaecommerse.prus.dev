<div class="mt-8">
    <h2 class="text-lg font-semibold">{{ __('Customer reviews') }}</h2>

    @if ($reviews->isEmpty())
        <p class="text-sm text-gray-500 mt-2">{{ __('No reviews yet.') }}</p>
    @else
        <ul class="mt-4 space-y-4">
            @foreach ($reviews as $r)
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
    @endif
</div>
