<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
    @forelse ($products as $product)
        @include('livewire.home.partials.product-card', [
            'product' => $product,
            'preset' => $preset,
            'attributes' => new \Illuminate\View\ComponentAttributeBag(),
        ])
    @empty
        <div class="col-span-full rounded-3xl border border-white/10 bg-white/5 px-6 py-16 text-center text-sm text-white/60">
            {{ __('frontend/home.products.empty') }}
        </div>
    @endforelse
</div>
