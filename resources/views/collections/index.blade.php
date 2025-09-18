@php
    use Illuminate\Support\Str;
    use Illuminate\View\ComponentAttributeBag;
@endphp

<x-layouts.base :title="__('frontend/collections.meta.title') . ' - ' . config('app.name')">
    <x-slot:head>
        <x-meta
            :title="__('frontend/collections.meta.title') . ' - ' . config('app.name')"
            :description="__('frontend/collections.meta.description')"
            canonical="{{ url()->current() }}" />
    </x-slot:head>

    <div class="bg-white text-slate-900">
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-white via-slate-50 to-blue-50"></div>
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\"80\" height=\"80\" viewBox=\"0 0 80 80\" xmlns=\"http://www.w3.org/2000/svg\"%3E%3Cg fill=\"none\" stroke=\"%2363748f\" stroke-opacity=\"0.08\"%3E%3Cpath d=\"M0 79.5H79.5V0\"/%3E%3C/g%3E%3C/svg%3E');"></div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-16 lg:pb-24 space-y-6">
                <span class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-4 py-1 text-xs font-semibold uppercase tracking-[0.35em] text-blue-600">
                    {{ __('frontend/collections.hero.badge') }}
                </span>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-heading font-semibold leading-tight text-balance text-slate-900">
                    {{ __('frontend/collections.hero.title') }}
                </h1>
                <p class="text-base sm:text-lg text-slate-600 leading-relaxed max-w-2xl">
                    {{ __('frontend/collections.hero.subtitle') }}
                </p>
            </div>
        </section>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">
            @forelse ($collections as $collection)
                @php
                    $collectionName = $collection->getTranslatedName() ?? $collection->name;
                    $collectionDescription = $collection->getTranslatedDescription() ?? $collection->description;
                    $image = $collection->getBannerUrl('lg') ?: $collection->getImageUrl('lg') ?: $collection->getImageUrl();
                    $products = $collection->products ?? collect();
                    $productCount = $collection->published_products_count ?? ($collection->products_count ?? $products->count());
                    $typeKey = $collection->is_automatic ? 'automatic' : 'manual';
                @endphp

                <article class="relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl">
                    <div class="absolute inset-0 bg-gradient-to-br from-white via-blue-50/40 to-slate-100/70"></div>
                    <div class="relative flex flex-col gap-10 p-8 sm:p-10 lg:p-12">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                            <div class="space-y-5 max-w-3xl">
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.3em] text-blue-600">
                                        {{ __('frontend/collections.types.' . $typeKey) }}
                                    </span>
                                    <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.3em] text-slate-500">
                                        {{ trans_choice('frontend/collections.stats.products', $productCount, ['count' => $productCount]) }}
                                    </span>
                                </div>

                                <h2 class="text-3xl sm:text-4xl font-heading font-semibold leading-tight text-slate-900">
                                    {{ $collectionName }}
                                </h2>

                                @if ($collectionDescription)
                                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                                        {{ Str::limit(strip_tags($collectionDescription), 220) }}
                                    </p>
                                @endif
                            </div>

                            <div class="w-full max-w-xs self-stretch overflow-hidden rounded-2xl border border-slate-200 bg-white">
                                @if ($image)
                                    <img src="{{ $image }}" alt="{{ $collectionName }}" class="h-full w-full object-cover" loading="lazy">
                                @else
                                    <div class="flex h-full min-h-[180px] items-center justify-center bg-gradient-to-br from-blue-100 to-indigo-100 text-4xl font-semibold text-blue-600">
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
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
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
                            <a href="{{ route('collections.show', $collection) }}"
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

            @if ($collections instanceof \Illuminate\Contracts\Pagination\Paginator)
                <div class="flex justify-center">
                    {{ $collections->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.base>
