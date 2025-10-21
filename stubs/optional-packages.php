<?php

declare(strict_types=1);

namespace Darryldecode\Cart;

use Illuminate\Support\Collection;

if (! class_exists(CartCollection::class)) {
    class CartCollection extends Collection {}
}

if (! class_exists(Cart::class)) {
    class Cart
    {
        public function getContent(): CartCollection
        {
            return new CartCollection;
        }

        public function getSubTotal(): float
        {
            return 0.0;
        }

        public function remove(string|int $id): void {}

        public function isEmpty(): bool
        {
            return true;
        }

        public function getTotalQuantity(): int
        {
            return 0;
        }

        public function clear(): void {}
    }
}

namespace Darryldecode\Cart\Facades;

use Darryldecode\Cart\Cart;

if (! class_exists(CartFacade::class)) {
    class CartFacade
    {
        public static function session(?string $sessionKey = null): Cart
        {
            return new Cart;
        }
    }
}
