@section('meta')
    @php
        $ogImage =
            $product->getFirstMediaUrl(config('media.storage.collection_name'), 'large') ?:
            $product->getFirstMediaUrl(config('media.storage.collection_name'));
    @endphp
    <x-meta
            :title="$product->name"
            :description="Str::limit(strip_tags($product->description), 150)"
            :og-image="$ogImage"
            ogType="product"
            :canonical="url()->current()"
            :preload-image="(string) ($ogImage ?: '')" />
@endsection

<div class="bg-white">
    <div class="pb-16 pt-6 sm:pb-24">
        <nav aria-label="Breadcrumb" class="mx-auto max-w-8xl px-4">
            <ol role="list" class="flex items-center space-x-4">
                <li>
                    <div class="flex items-center">
                        <a href="#"
                           class="mr-4 text-sm font-medium text-gray-900">{{ __('breadcrumbs.department') }}</a>
                        <svg viewBox="0 0 6 20" aria-hidden="true" class="h-5 w-auto text-gray-300">
                            <path d="M4.878 4.34H3.551L.27 16.532h1.327l3.281-12.19z" fill="currentColor" />
                        </svg>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <a href="#"
                           class="mr-4 text-sm font-medium text-gray-900">{{ __('breadcrumbs.category') }}</a>
                        <svg viewBox="0 0 6 20" aria-hidden="true" class="h-5 w-auto text-gray-300">
                            <path d="M4.878 4.34H3.551L.27 16.532h1.327l3.281-12.19z" fill="currentColor" />
                        </svg>
                    </div>
                </li>
                <li class="text-sm">
                    <span aria-current="page" class="font-medium text-gray-500">
                        {{ $product->name }}
                    </span>
                </li>
            </ol>
        </nav>
        <x-container class="mt-8 max-w-2xl">
            <div class="lg:grid lg:auto-rows-min lg:grid-cols-12 lg:gap-x-8">
                <div class="lg:col-span-5 lg:col-start-8">
                    <div class="flex justify-between">
                        <h1 class="font-heading text-xl font-semibold text-gray-900 lg:text-2xl">
                            {{ $product->name }}
                        </h1>
                        <x-product.price :product="$product" class="text-xl font-medium text-gray-900" />
                    </div>
                    <!-- Reviews -->
                    <div class="mt-4">
                        <h2 class="sr-only">{{ __('product.reviews.title') }}</h2>
                        <div class="flex items-center">
                            <p class="text-sm text-gray-700">
                                3.9
                                <span class="sr-only">{{ __('product.reviews.out_of_five') }}</span>
                            </p>
                            <div class="ml-1 flex items-center">
                                <!-- Active: "text-yellow-400", Inactive: "text-gray-200" -->
                                <svg
                                     class="size-5 flex-shrink-0 text-yellow-400"
                                     viewBox="0 0 20 20"
                                     fill="currentColor"
                                     aria-hidden="true">
                                    <path
                                          fill-rule="evenodd"
                                          d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
                                          clip-rule="evenodd" />
                                </svg>
                                <svg
                                     class="size-5 flex-shrink-0 text-yellow-400"
                                     viewBox="0 0 20 20"
                                     fill="currentColor"
                                     aria-hidden="true">
                                    <path
                                          fill-rule="evenodd"
                                          d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
                                          clip-rule="evenodd" />
                                </svg>
                                <svg
                                     class="size-5 flex-shrink-0 text-yellow-400"
                                     viewBox="0 0 20 20"
                                     fill="currentColor"
                                     aria-hidden="true">
                                    <path
                                          fill-rule="evenodd"
                                          d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
                                          clip-rule="evenodd" />
                                </svg>
                                <svg
                                     class="size-5 flex-shrink-0 text-yellow-400"
                                     viewBox="0 0 20 20"
                                     fill="currentColor"
                                     aria-hidden="true">
                                    <path
                                          fill-rule="evenodd"
                                          d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
                                          clip-rule="evenodd" />
                                </svg>
                                <svg
                                     class="size-5 flex-shrink-0 text-gray-200"
                                     viewBox="0 0 20 20"
                                     fill="currentColor"
                                     aria-hidden="true">
                                    <path
                                          fill-rule="evenodd"
                                          d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
                                          clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div aria-hidden="true" class="ml-4 text-sm text-gray-300">·</div>
                            <div class="ml-4 flex">
                                <span class="text-sm font-medium text-primary-600">
                                    {{ trans_choice('product.reviews.count', 512, ['count' => 512]) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product gallery -->
                <div class="mt-8 lg:col-span-7 lg:col-start-1 lg:row-span-3 lg:row-start-1 lg:mt-0">
                    <h2 class="sr-only">{{ $product->name }} {{ __('product.images') }}</h2>

                    <div class="grid grid-cols-1 lg:grid-cols-2 lg:grid-rows-3 lg:gap-8">
                        <div class="lg:col-span-2 lg:row-span-2">
                            @php($thumb = $product->getFirstMediaUrl(config('media.storage.thumbnail_collection')) ?: ($product->getFirstMediaUrl(config('media.storage.collection_name'), 'small') ?: ($product->getFirstMediaUrl(config('media.storage.collection_name'), 'medium') ?: $product->getFirstMediaUrl(config('media.storage.collection_name')))))
                            @if ($thumb)
                                <img
                                     src="{{ $thumb }}"
                                     alt="{{ $product->name }} {{ __('product.thumbnail') }}"
                                     class="h-full w-full object-cover" />
                            @endif
                        </div>
                        <x-product.gallery
                                           :images="$product->getMedia(config('media.storage.collection_name'))"
                                           :product="$product" />
                    </div>
                </div>

                <div class="mt-8 lg:col-span-5">
                    <livewire:components.variants-selector :$product />

                    <!-- Product details -->
                    <div class="mt-10">
                        <h2 class="text-sm font-medium text-gray-900">{{ __('Description') }}</h2>

                        <div class="prose prose-sm mt-4 text-gray-500">
                            {!! $product->description !!}
                        </div>
                    </div>

                    <x-product.additionnal-infos />
                </div>
            </div>
        </x-container>
    </div>
</div>

@push('scripts')
    @php
        $productUrl = route('product.show', [
            'locale' => app()->getLocale(),
            'slug' => $product->trans('slug') ?? $product->slug,
        ]);
        $image =
            $product->getFirstMediaUrl(config('media.storage.collection_name'), 'large') ?:
            $product->getFirstMediaUrl(config('media.storage.collection_name'));
        $offers = null;
        if (method_exists($product, 'getPrice')) {
            $price = $product->getPrice();
            if ($price && $price->value) {
                $offers = [
                    '@type' => 'Offer',
                    'priceCurrency' => current_currency(),
                    'price' => number_format(($price->value->amount ?? 0) / 100, 2, '.', ''),
                    'availability' => 'https://schema.org/' . ($product->isOutOfStock() ? 'OutOfStock' : 'InStock'),
                    'url' => $productUrl,
                ];
            }
        }

        // Basic breadcrumb items (department -> category -> product). Replace placeholders if real data exists.
        $breadcrumbItems = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => __('breadcrumbs.department'),
                'item' => url('/'),
            ],
        ];
        $position = 2;
        // If a category relation exists, include it
        try {
            if (isset($product->categories) && $product->categories->first()) {
                $cat = $product->categories->first();
                $breadcrumbItems[] = [
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => $cat->trans('name') ?? $cat->name,
                    'item' => route('localized.categories.show', [
                        'locale' => app()->getLocale(),
                        'category' => $cat->trans('slug') ?? $cat->slug,
                    ]),
                ];
            }
        } catch (Throwable $e) {
            // ignore if relation missing
        }
        $breadcrumbItems[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $product->trans('name') ?? $product->name,
            'item' => $productUrl,
        ];

        $productSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->trans('name') ?? $product->name,
            'description' => Str::limit(strip_tags($product->trans('description') ?? $product->description), 300),
            'image' => $image ? [$image] : [],
            'sku' => $product->sku ?? null,
            'brand' =>
                $product->brand->name ?? null
                    ? [
                        '@type' => 'Brand',
                        'name' => $product->brand->trans('name') ?? $product->brand->name,
                    ]
                    : null,
            'offers' => $offers,
            'url' => $productUrl,
        ];
        // Remove nulls
        $productSchema = array_filter($productSchema, fn($v) => $v !== null && $v !== []);
    @endphp
    <script type="application/ld+json">
        {!! json_encode($productSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $breadcrumbItems], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush
