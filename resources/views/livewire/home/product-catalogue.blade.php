<div class="space-y-10">
    <div class="flex flex-col gap-4 rounded-3xl border border-white/10 bg-white/5 p-6 shadow-lg backdrop-blur">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex flex-1 items-center gap-3">
                <div class="relative flex-1">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-white/50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input
                        type="search"
                        wire:model.debounce.500ms="search"
                        placeholder="{{ __('frontend/home.catalogue.search_placeholder') }}"
                        class="w-full rounded-full border-0 bg-white/10 px-12 py-3 text-sm text-white placeholder:text-white/40 focus:outline-none focus:ring-4 focus:ring-indigo-400/40"
                    />
                </div>

                <div class="relative">
                    <select
                        wire:model.live="category"
                        class="rounded-full border-0 bg-white/10 px-5 py-3 text-sm text-white focus:outline-none focus:ring-4 focus:ring-indigo-400/40">
                        <option value="">{{ __('frontend/home.catalogue.filters.all_categories') }}</option>
                        @foreach ($categories as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-xs uppercase tracking-[0.25em] text-white/60">
                    {{ __('frontend/home.catalogue.filters.sort_by') }}
                </span>
                <select
                    wire:model.live="sort"
                    class="rounded-full border-0 bg-white/10 px-5 py-3 text-sm text-white focus:outline-none focus:ring-4 focus:ring-indigo-400/40">
                    <option value="latest">{{ __('frontend/home.catalogue.sort.latest') }}</option>
                    <option value="popular">{{ __('frontend/home.catalogue.sort.popular') }}</option>
                    <option value="price_asc">{{ __('frontend/home.catalogue.sort.price_asc') }}</option>
                    <option value="price_desc">{{ __('frontend/home.catalogue.sort.price_desc') }}</option>
                </select>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($products as $product)
            @include('livewire.home.partials.product-card', [
                'product' => $product,
                'preset' => $sort === 'latest' ? 'latest' : ($sort === 'popular' ? 'trending' : 'featured'),
                'attributes' => new \Illuminate\View\ComponentAttributeBag(),
            ])
        @empty
            <div class="col-span-full rounded-3xl border border-white/10 bg-white/5 px-6 py-16 text-center text-sm text-white/60">
                {{ __('frontend/home.catalogue.empty') }}
            </div>
        @endforelse
    </div>

    <div class="flex justify-center">
        {{ $products->onEachSide(1)->links() }}
    </div>
</div>
