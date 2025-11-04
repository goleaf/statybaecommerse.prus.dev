
<div class="space-y-10">
    <x-breadcrumbs :items="[['label' => __('my_account'), 'url' => route('account.index')], ['label' => __('Wishlist')]]" />
    <x-page-heading :title="__('Wishlist')" :description="__('products_you_saved_for_later')" />

    @if (empty($wishlists))
        <p class="text-sm text-gray-500">{{ __('your_wishlist_is_empty') }}</p>
    @else
        <div class="space-y-8">
            @foreach ($wishlists as $list)
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">{{ $list['name'] ?? __('Wishlist') }}</h3>
                        @if (($list['is_default'] ?? false) === true)
                            <span class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-700">{{ __('default') }}</span>
                        @endif
                    </div>
                    @php($items = $list['items'] ?? [])
                    @if (count($items) === 0)
                        <p class="text-sm text-gray-500">{{ __('no_items_in_this_list') }}</p>
                    @else
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                            @foreach ($items as $item)
                                <x-shared.product-card :product="$item['product'] ?? null" />
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
