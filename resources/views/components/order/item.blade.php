@props([
    'item',
    'currency_code',
])

@php
    use Illuminate\Support\Number;

    $currency   = (string) ($currency_code ?? 'EUR');
    $locale     = app()->getLocale();
    $unitPrice  = (float) ($item->unit_price_amount ?? $item->unit_price ?? $item->price ?? 0);
    $lineTotal  = (float) ($item->total ?? ($unitPrice * (int) $item->quantity));

    // Resolve product URL
    $productUrl = null;
    if ($item->product) {
        $slug = $item->product->trans('slug') ?? $item->product->slug ?? null;
        if ($slug && \Illuminate\Support\Facades\Route::has('product.show')) {
            try { $productUrl = route('product.show', $slug); } catch (\Throwable) {}
        }
        if (! $productUrl && \Illuminate\Support\Facades\Route::has('frontend.products.show')) {
            try { $productUrl = route('frontend.products.show', ['product' => $slug ?? $item->product->getKey()]); } catch (\Throwable) {}
        }
    }

    // Resolve product image
    $imageUrl = null;
    if ($item->product && method_exists($item->product, 'getImageUrl')) {
        $imageUrl = $item->product->getImageUrl('thumb') ?? $item->product->getImageUrl('sm') ?? $item->product->getImageUrl();
    }
    if (! $imageUrl && $item->product) {
        $imageUrl = $item->product->main_image ?? $item->product->thumbnail ?? null;
    }
    $fallback = asset('images/placeholder-product.jpg');

    // Variant attributes — use built-in JSON column first, then pivot relation as fallback
    $variantAttributes = [];
    $variantName = null;
    if ($item->productVariant) {
        $variantName = $item->productVariant->name ?? null;
        $variantAttributes = $item->productVariant->getVariantAttributes();
    }

    // Try to extract variant from item name if no relation (name = "Product – Variant")
    $itemName = (string) $item->name;
    if (! $variantName && str_contains($itemName, ' - ')) {
        [$productName, $variantName] = array_map('trim', explode(' - ', $itemName, 2));
    } else {
        $productName = $itemName;
    }

    $sku = $item->sku ?? $item->product?->sku ?? null;
@endphp

<li class="grid grid-cols-12 items-center gap-3 px-4 py-4 sm:gap-4">

    {{-- ── Col 1-6: Image + Name + Variant ─────────────────────── --}}
    <div class="col-span-12 flex items-center gap-3 sm:col-span-6">

        {{-- Thumbnail (small) --}}
        <div class="size-14 shrink-0 overflow-hidden rounded-lg border border-gray-100 bg-gray-50">
            @if ($productUrl)
                <a href="{{ $productUrl }}" tabindex="-1">
                    <img src="{{ $imageUrl ?? $fallback }}"
                         alt="{{ $productName }}"
                         class="size-full object-cover"
                         loading="lazy">
                </a>
            @else
                <img src="{{ $imageUrl ?? $fallback }}"
                     alt="{{ $productName }}"
                     class="size-full object-cover"
                     loading="lazy">
            @endif
        </div>

        {{-- Name + meta --}}
        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold text-gray-900">
                @if ($productUrl)
                    <a href="{{ $productUrl }}" class="hover:text-blue-600 transition-colors">
                        {{ $productName }}
                    </a>
                @else
                    {{ $productName }}
                @endif
            </p>

            {{-- SKU --}}
            @if ($sku)
                <p class="mt-0.5 text-xs text-gray-400">SKU: {{ $sku }}</p>
            @endif

            {{-- Variant pill(s) --}}
            @if ($variantName || ! empty($variantAttributes))
                <div class="mt-1.5 flex flex-wrap gap-1.5">
                    @if (! empty($variantAttributes))
                        @foreach ($variantAttributes as $label => $val)
                            <span class="inline-flex items-center gap-1 rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5 text-[11px] font-medium text-gray-600">
                                <span class="text-gray-400">{{ $label }}:</span> {{ $val }}
                            </span>
                        @endforeach
                    @elseif ($variantName)
                        <span class="inline-flex items-center rounded-full border border-blue-100 bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700">
                            {{ $variantName }}
                        </span>
                    @endif
                </div>
            @endif

            {{-- Mobile-only: qty × price --}}
            <p class="mt-1 text-xs text-gray-400 sm:hidden">
                {{ $item->quantity }}× {{ Number::currency($unitPrice, $currency, $locale) }}
            </p>
        </div>
    </div>

    {{-- ── Col 7-8: Qty ─────────────────────────────────────────── --}}
    <div class="col-span-4 hidden text-center sm:col-span-2 sm:block">
        <span class="inline-flex size-7 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-700">
            {{ $item->quantity }}
        </span>
    </div>

    {{-- ── Col 9-10: Unit price ──────────────────────────────────── --}}
    <div class="col-span-4 hidden text-right text-sm text-gray-500 sm:col-span-2 sm:block">
        {{ Number::currency($unitPrice, $currency, $locale) }}
    </div>

    {{-- ── Col 11-12: Line total ────────────────────────────────── --}}
    <div class="col-span-8 text-right sm:col-span-2">
        <span class="text-sm font-semibold text-gray-900">
            {{ Number::currency($lineTotal, $currency, $locale) }}
        </span>
    </div>

</li>
