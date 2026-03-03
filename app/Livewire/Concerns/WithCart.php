<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Cart\CartService;

/**
 * WithCart
 *
 * Trait providing reusable functionality across multiple classes.
 */
trait WithCart
{
    /**
     * Attempt to add the given product to the active cart session.
     *
     * Returns false when the action bails out (e.g. missing product or insufficient stock)
     * so consuming components can avoid firing auxiliary side effects.
     */
    public function addToCart(int $productId, int $quantity = 1, ?string $successMessage = null, ?int $variantId = null): bool
    {
        $product = Product::query()->find($productId);

        if ($product === null) {
            $this->notifyError(__('messages.the_selected_product_is_no_longer_available'));

            return false;
        }

        if ($product->shouldHideAddToCart()) {
            $this->notifyWarning(__('messages.this_product_is_not_available_for_online_purchase'));

            return false;
        }

        $normalizedQuantity = max(1, $quantity);

        $variant = null;

        if ($variantId !== null) {
            $variant = ProductVariant::query()
                ->where('product_id', $product->getKey())
                ->find($variantId);

            if ($variant === null) {
                $this->notifyError(__('messages.the_selected_variant_is_no_longer_available'));

                return false;
            }

            if (! $variant->isAvailableForPurchase()) {
                $this->notifyWarning(__('messages.this_variant_is_not_available_for_purchase'));

                return false;
            }

            if ($variant->track_inventory && $variant->availableQuantity() < $normalizedQuantity) {
                $this->notifyError(__('messages.not_enough_stock_available'));

                return false;
            }
        } elseif ($product->isVariant()) {
            // For variant products, fall back to a sensible default variant when none is selected.
            $variant = ProductVariant::query()
                ->where('product_id', $product->getKey())
                ->orderByDesc('is_default_variant')
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->get()
                ->first(static function (ProductVariant $candidate) use ($normalizedQuantity): bool {
                    if (! $candidate->isAvailableForPurchase()) {
                        return false;
                    }

                    return ! $candidate->track_inventory
                        || $candidate->availableQuantity() >= $normalizedQuantity;
                });

            if ($variant === null) {
                $this->notifyWarning(__('messages.this_product_is_not_available_for_purchase'));

                return false;
            }
        } elseif ($product->availableQuantity() < $normalizedQuantity) {
            $this->notifyError(__('messages.not_enough_stock_available'));

            return false;
        }

        $this->persistCartItem($product, $normalizedQuantity, $successMessage, $variant);

        return true;
    }

    /**
     * Persist the cart item snapshot and trigger downstream UI updates.
     */
    protected function persistCartItem(Product $product, int $quantity = 1, ?string $successMessage = null, ?ProductVariant $variant = null): void
    {
        $cartItems = session()->get('cart', []);
        $cartKey = $this->resolveCartKey($product->getKey(), $variant?->getKey());
        $unitPrice = $variant ? (float) $variant->getCurrentPrice() : (float) ($product->price ?? 0);
        $variantAttributes = [];

        if ($variant !== null) {
            $attributeValues = $variant->attributes()->with('attribute')->get();

            if ($attributeValues->isNotEmpty()) {
                $variantAttributes = $attributeValues
                    ->mapWithKeys(static function ($value): array {
                        $attribute = $value->attribute;

                        if ($attribute === null || $attribute->name === null) {
                            return [];
                        }

                        return [(string) $attribute->name => (string) $value->value];
                    })
                    ->toArray();
            }
        }

        if (isset($cartItems[$cartKey])) {
            $cartItems[$cartKey]['quantity'] += $quantity;
        } else {
            $cartItems[$cartKey] = [
                'product_id' => $product->getKey(),
                'variant_id' => $variant?->getKey(),
                'name'       => $variant?->name ?: $product->name,
                'price'      => $unitPrice,
                'quantity'   => $quantity,
                'image'      => $this->resolveProductCartImage($product),
                'sku'        => $variant?->sku ?? $product->sku,
                'attributes' => $variantAttributes,
            ];
        }

        session()->put('cart', $cartItems);

        $this->dispatch(
            'add-to-cart',
            productId: (int) $product->getKey(),
            quantity: $quantity,
            variantId: $variant?->getKey()
        );

        $this->dispatch('cart-updated');
        $this->notifySuccess($successMessage ?? __('messages.product_added_to_cart'));
    }

    public function removeFromCart(int $productId, ?int $variantId = null): void
    {
        $cartItems = session()->get('cart', []);
        $cartKey = $this->resolveCartKey($productId, $variantId);
        if (isset($cartItems[$cartKey])) {
            unset($cartItems[$cartKey]);
            session()->put('cart', $cartItems);
            $this->dispatch('cart-updated');
            $this->notifySuccess(__('messages.product_removed_from_cart'));
        }
    }

    public function updateCartQuantity(int $productId, int $quantity, ?int $variantId = null): void
    {
        if ($quantity <= 0) {
            $this->removeFromCart($productId, $variantId);

            return;
        }
        $product = Product::find($productId);
        if (! $product || $product->stock_quantity < $quantity) {
            $this->notifyError(__('messages.not_enough_stock_available'));

            return;
        }
        $cartItems = session()->get('cart', []);
        $cartKey = $this->resolveCartKey($productId, $variantId);
        if (isset($cartItems[$cartKey])) {
            $cartItems[$cartKey]['quantity'] = $quantity;
            session()->put('cart', $cartItems);
            $this->dispatch('cart-updated');
        }
    }

    public function getCartCount(): int
    {
        return app(CartService::class)->getSessionCount();
    }

    public function getCartTotal(): float
    {
        $cartItems = session()->get('cart', []);
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $total;
    }

    public function clearCart(): void
    {
        session()->forget('cart');
        $this->dispatch('cart-updated');
        $this->notifySuccess(__('messages.cart_cleared'));
    }

    private function resolveProductCartImage(Product $product): ?string
    {
        $candidates = [
            $product->main_image,
            $product->thumbnail,
            $product->getImageUrl(),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        $collection = (string) config('media.storage.collection_name', 'images');
        $mediaImage = $product->getFirstMediaUrl($collection, 'thumb')
            ?: $product->getFirstMediaUrl($collection);

        return is_string($mediaImage) && $mediaImage !== '' ? $mediaImage : null;
    }

    private function resolveCartKey(int $productId, ?int $variantId): string
    {
        return $variantId === null ? (string) $productId : $productId . ':' . $variantId;
    }
}
