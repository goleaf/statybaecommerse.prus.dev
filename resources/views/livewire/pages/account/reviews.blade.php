{{-- Reviews Component --}}
<div class="space-y-10">
    <x-breadcrumbs :items="[['label' => __('frontend.account.nav.title'), 'url' => route('account.index')], ['label' => __('frontend.account.reviews')]]" />
        <x-page-heading :title="__('frontend.account.reviews')" />

        @if ($reviews->isEmpty())
            <p class="text-gray-500">{{ __('frontend.account.reviews_empty') }}</p>
        @else
            <div class="divide-y divide-gray-200 rounded-md border border-gray-200">
                @foreach ($reviews as $r)
                    <div class="p-4 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="text-yellow-500">{{ str_repeat('★', (int) ($r->rating ?? 0)) }}</span>
                            <span class="text-sm text-gray-500">{{ $r->created_at }}</span>
                        </div>
                        <div class="font-medium">{{ $r->title ?? __('frontend.account.reviews_fallback') }}</div>
                        <div class="text-sm text-gray-700">{{ $r->content ?? '' }}</div>
                    </div>
                @endforeach
            </div>
        @endif
</div>
