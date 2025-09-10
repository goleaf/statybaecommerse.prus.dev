<?php
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.templates.account')] class extends Component {
    public array $wishlists = [];

    public function mount(): void
    {
        $user = auth()->user();
        if ($user) {
            $this->wishlists = \App\Models\UserWishlist::query()
                ->where('user_id', $user->id)
                ->with(['items.product', 'items.variant'])
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get()
                ->toArray();
        }
    }
}; ?>

<div class="space-y-10">
    <x-breadcrumbs :items="[['label' => __('My account'), 'url' => route('account')], ['label' => __('Wishlist')]]" />
    <x-page-heading :title="__('Wishlist')" :description="__('Products you saved for later')" />

    @if (empty($wishlists))
        <p class="text-sm text-gray-500">{{ __('Your wishlist is empty.') }}</p>
    @else
        <div class="space-y-8">
            @foreach ($wishlists as $list)
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">{{ $list['name'] ?? __('Wishlist') }}</h3>
                        @if (($list['is_default'] ?? false) === true)
                            <span class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-700">{{ __('Default') }}</span>
                        @endif
                    </div>
                    @php($items = $list['items'] ?? [])
                    @if (count($items) === 0)
                        <p class="text-sm text-gray-500">{{ __('No items in this list.') }}</p>
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
