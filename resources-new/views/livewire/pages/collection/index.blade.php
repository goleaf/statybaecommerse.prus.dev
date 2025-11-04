@php
    use Illuminate\Support\Str;
    use Illuminate\View\ComponentAttributeBag;
@endphp

@section('meta')
    <x-meta
        :title="__('frontend/collections.meta.title') . ' - ' . config('app.name')"
        :description="__('frontend/collections.meta.description')"
        canonical="{{ url()->current() }}" />
@endsection

<div class="bg-sage text-dark">
    {{-- Hero / Banner (dark/sage) --}}
    <section class="relative overflow-hidden bg-dark text-sage border-b border-ash/30">
        <div class="absolute top-0 right-0 w-32 h-32 bg-sage/10 rotate-45 transform translate-x-16 -translate-y-16"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-sage/10 rotate-45 transform -translate-x-12 translate-y-12"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-12 lg:pb-16">
            <div class="space-y-6 max-w-3xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-sage bg-sage px-4 py-1 text-xs font-semibold uppercase tracking-[0.35em] text-dark">
                    {{ __('frontend/collections.hero.badge') }}
                </span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-heading font-semibold leading-tight text-balance">
                    {{ __('frontend/collections.hero.title') }}
                </h1>
                <p class="text-base sm:text-lg text-ash leading-relaxed max-w-2xl">
                    {{ __('frontend/collections.hero.subtitle') }}
                </p>
            </div>
        </div>
    </section>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">
        @forelse ($collections as $collection)
            @php
                $image = $collection->getBannerUrl('lg') ?: $collection->getImageUrl('lg') ?: $collection->getImageUrl();
                $collectionName = $collection->getTranslatedName() ?? $collection->name;
                $collectionDescription = $collection->getTranslatedDescription() ?? $collection->description;
                $products = $collection->products;
                $productCount = $collection->published_products_count ?? ($collection->products_count ?? $products->count());
                $typeKey = $collection->is_automatic ? 'automatic' : 'manual';
            @endphp

            <article class="relative overflow-hidden border border-slate-200 bg-dark shadow-xl">
                <div class="relative flex flex-col gap-10 p-8 sm:p-10 lg:p-12">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                        <div class="space-y-5 max-w-3xl">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="inline-flex items-center gap-2 rounded-full border border-sage bg-sage px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.3em] text-dark">
                                    {{ __('frontend/collections.types.' . $typeKey) }}
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full border border-ash/30 bg-ash/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.3em] text-dark">
                                    {{ trans_choice('frontend/collections.stats.products', $productCount, ['count' => $productCount]) }}
                                </span>
                            </div>

                            <h2 class="text-3xl sm:text-4xl font-heading font-semibold leading-tight text-sage">
                                {{ $collectionName }}
                            </h2>

                            @if ($collectionDescription)
                                <p class="text-sm sm:text-base text-ash leading-relaxed">
                                    {{ Str::limit(strip_tags($collectionDescription), 220) }}
                                </p>
                            @endif
                        </div>

                        <div class="w-full max-w-xs self-stretch overflow-hidden rounded-2xl border border-slate-200 bg-white">
                            @if ($image)
                                <img src="{{ $image }}" alt="{{ $collectionName }}" class="h-full w-full object-cover" loading="lazy">
                            @else
                                <div class="flex h-full min-h-[180px] items-center justify-center bg-ash/20 text-4xl font-semibold text-dark">
                                    {{ Str::upper(mb_substr($collectionName, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($products->isEmpty())
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
                            {{ __('frontend/collections.empty.products') }}
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($products as $product)
                                @include('livewire.home.partials.product-card', [
                                    'product' => $product,
                                    'preset' => 'featured',
                                    'attributes' => new ComponentAttributeBag(),
                                ])
                            @endforeach
                        </div>
                    @endif

                    <div class="flex justify-end">
                        <a href="{{ route('collections.show', ['locale' => app()->getLocale(), 'collection' => $collection->getTranslatedSlug() ?? $collection->slug]) }}"
                           class="inline-flex items-center gap-2 rounded-full bg-indigo-500 px-5 py-3 text-xs font-semibold text-white shadow-lg transition hover:bg-indigo-600">
                            {{ __('frontend/collections.buttons.view_collection') }}
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-slate-200 bg-slate-50 px-6 py-16 text-center text-sm text-slate-500">
                {{ __('frontend/collections.empty.collections') }}
            </div>
        @endforelse
    </div>
</div>
