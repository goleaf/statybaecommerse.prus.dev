<div class="space-y-10">


    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($products as $product)
            @include('livewire.home.partials.product-card', [
                'product' => $product,
                'preset' => $sort === 'latest' ? 'latest' : ($sort === 'popular' ? 'trending' : 'featured'),
                'attributes' => new \Illuminate\View\ComponentAttributeBag(),
            ])
        @empty
            <div
                 class="col-span-full rounded-3xl border border-white/10 bg-white/5 px-6 py-16 text-center text-sm text-white/60">
                {{ __('frontend/home.catalogue.empty') }}
            </div>
        @endforelse
    </div>

    <div class="flex justify-center">
        {{ $products->onEachSide(1)->links() }}
    </div>
</div>
