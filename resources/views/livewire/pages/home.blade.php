@section('meta')
    @php
        $websiteJsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'url' => url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => route('search.index', ['locale' => app()->getLocale()]) . '?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    @endphp
    <x-meta
            :title="__('nav_home') . ' - ' . config('app.name')"
            :description="__('meta_description_home')"
            :og-image="Vite::asset('resources/images/hero.png')"
            canonical="{{ url()->current() }}"
            :jsonld="json_encode($websiteJsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)" />
@endsection
<div class="relative isolate overflow-hidden" wire:loading.attr="aria-busy" aria-busy="false">
    @if (session('status'))
        <x-container><x-alert type="success" class="mb-4">{{ session('status') }}</x-alert></x-container>
    @endif
    @if (session('error'))
        <x-container><x-alert type="error" class="mb-4">{{ session('error') }}</x-alert></x-container>
    @endif
    <svg
         class="absolute inset-0 -z-10 h-full w-full stroke-gray-200 [mask-image:radial-gradient(100%_100%_at_top_right,white,transparent)]"
         aria-hidden="true">
        <defs>
            <pattern
                     id="0787a7c5-978c-4f66-83c7-11c213f99cb7"
                     width="200"
                     height="200"
                     x="50%"
                     y="-1"
                     patternUnits="userSpaceOnUse">
                <path d="M.5 200V.5H200" fill="none" />
            </pattern>
        </defs>
        <rect width="100%" height="100%" stroke-width="0" fill="url(#0787a7c5-978c-4f66-83c7-11c213f99cb7)" />
    </svg>

    <x-container class="relative py-16 sm:pt-24 lg:py-40 lg:flex lg:items-center lg:gap-x-10">
        <div class="sm:max-w-xl">
            <div>
                <h1 class="font font-heading text-4xl font-extrabold tracking-tight text-black sm:text-6xl hero-title">
                    {{ __('home_new_arrivals') }}
                </h1>
                <p class="mt-4 text-xl text-gray-500">
                    {{ __('home_new_arrivals_desc') }}
                </p>
            </div>
            <div class="py-10">
                <x-buttons.primary href="#" class="group px-8 py-3 text-center text-base font-medium hero-cta">
                    {{ __('home_shop_now') }}
                    <span
                          class="ml-2 translate-x-0 transform transition duration-200 ease-in-out group-hover:translate-x-1">
                        <x-untitledui-arrow-narrow-right class="size-6" stroke-width="1.5" aria-hidden="true" />
                    </span>
                </x-buttons.primary>
            </div>
        </div>
        <div class="mt-16 sm:mt-24 lg:mt-0 lg:shrink-0 lg:grow">
            <link rel="preload" as="image" href="{{ Vite::asset('resources/images/hero.png') }}"
                  imagesrcset="{{ Vite::asset('resources/images/hero.png') }} 1x"
                  imagesizes="(max-width: 1024px) 90vw, 768px">
            <img class="h-auto object-cover lg:max-w-3xl mx-auto" src="{{ Vite::asset('resources/images/hero.png') }}"
                 alt="" width="768" height="512" loading="eager" fetchpriority="high" />
        </div>
    </x-container>

    <x-stats />

    <div class="bg-gray-50">
        <x-container class="py-16 lg:py-24">
            @if ($brands->isNotEmpty())
                <section aria-labelledby="brand-heading" class="mx-auto max-w-xl lg:max-w-none mb-16">
                    <h2 id="brand-heading"
                        class="font-heading text-2xl font-extrabold tracking-tight text-gray-950 sm:text-3xl">
                        {{ __('nav_brands') }}
                    </h2>
                    <div class="mt-8 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-6">
                        @foreach ($brands as $brand)
                            <x-link :href="route('brand.show', [
                                'locale' => app()->getLocale(),
                                'slug' => $brand->trans('slug') ?? $brand->slug,
                            ])"
                                    class="group block border rounded-md p-4 bg-white hover:shadow-sm">
                                <div class="aspect-[3/2] flex items-center justify-center">
                                    @if ($brand->getLogoUrl('sm'))
                                        <img loading="lazy"
                                             src="{{ $brand->getLogoUrl('sm') }}"
                                             srcset="{{ $brand->getLogoUrl('xs') }} 64w, {{ $brand->getLogoUrl('sm') }} 128w, {{ $brand->getLogoUrl('md') }} 200w"
                                             sizes="128px"
                                             alt="{{ $brand->trans('name') ?? $brand->name }}"
                                             width="128" height="128"
                                             class="max-h-16 object-contain" />
                                    @elseif ($brand->getLogoUrl())
                                        <img loading="lazy" src="{{ $brand->getLogoUrl() }}"
                                             alt="{{ $brand->trans('name') ?? $brand->name }}"
                                             width="120" height="80"
                                             class="max-h-16 object-contain" />
                                    @else
                                        <span
                                              class="text-sm text-gray-600">{{ $brand->trans('name') ?? $brand->name }}</span>
                                    @endif
                                </div>
                            </x-link>
                        @endforeach
                    </div>
                </section>
            @endif
            @if ($collections->isNotEmpty())
                <section aria-labelledby="collection-heading" class="mx-auto max-w-xl lg:max-w-none">
                    <h2 id="collection-heading"
                        class="font-heading text-2xl font-extrabold tracking-tight text-gray-950 sm:text-3xl">
                        {{ __('home_shop_by_collection') }}
                    </h2>
                    <p class="mt-2 text-base/6 max-w-3xl text-gray-500">
                        {{ __('home_collections_desc') }}
                    </p>

                    <div class="mt-10 space-y-12 lg:grid lg:grid-cols-3 lg:gap-x-8 lg:space-y-0">
                        @foreach ($collections as $collection)
                            <x-link :href="route('collection.show', [
                                'locale' => app()->getLocale(),
                                'slug' => $collection->trans('slug') ?? $collection->slug,
                            ])" class="group block">
                                @if ($collection->getImageUrl('lg'))
                                    <img src="{{ $collection->getImageUrl('md') }}"
                                         srcset="{{ $collection->getImageUrl('sm') }} 200w, {{ $collection->getImageUrl('md') }} 400w, {{ $collection->getImageUrl('lg') }} 600w"
                                         sizes="(max-width: 1024px) 50vw, 600px"
                                         alt="{{ $collection->trans('name') ?? $collection->name }}"
                                         loading="lazy" width="600" height="400"
                                         class="aspect-[3/2] w-full object-cover group-hover:opacity-75 lg:aspect-[3/2]" />
                                @elseif ($collection->getImageUrl())
                                    <img src="{{ $collection->getImageUrl() }}"
                                         alt="{{ $collection->trans('name') ?? $collection->name }}"
                                         loading="lazy" width="600" height="400"
                                         class="aspect-[3/2] w-full object-cover group-hover:opacity-75 lg:aspect-[3/2]" />
                                @else
                                    <div class="aspect-[3/2] w-full bg-gray-200 flex items-center justify-center">
                                        <span
                                              class="text-lg text-gray-500 font-medium">{{ strtoupper(substr($collection->trans('name') ?? $collection->name, 0, 3)) }}</span>
                                    </div>
                                @endif
                                <h3 class="mt-2 text-base font-semibold text-gray-900">
                                    {{ $collection->trans('name') ?? $collection->name }}
                                </h3>
                            </x-link>
                        @endforeach
                    </div>
                </section>
            @endif

            <section aria-labelledby="products-list" class="mt-16 max-w-3xl lg:mt-32 lg:max-w-none">
                <h2 class="font-heading text-2xl font-semibold tracking-tight text-gray-950 sm:text-3xl">
                    {{ __('home_trending_products') }}
                </h2>

                <div class="mt-6 grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4 xl:gap-x-8">
                    @foreach ($products as $product)
                        <x-product.card :product="$product" />
                    @endforeach
                </div>
            </section>
        </x-container>
    </div>
</div>
