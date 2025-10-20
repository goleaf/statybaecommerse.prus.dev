<?php

namespace Darryldecode\Cart {
    use Illuminate\Support\Collection;
    /**
     * @template TKey of array-key
     * @template TValue
     * @extends Collection<TKey, TValue>
     */
    class CartCollection extends Collection {}

    class CartItem
    {
        public int $quantity = 0;

        /** @var int|float|string */
        public $price = 0;

        public string $name = '';

        /** @var object|null */
        public $associatedModel = null;
    }

    class Cart
    {
        /**
         * @return CartCollection<int, CartItem>
         */
        public function getContent()
        {
            return new CartCollection();
        }

        /**
         * @return int|float|string
         */
        public function getSubTotal()
        {
            return 0;
        }

        public function getTotalQuantity(): int
        {
            return 0;
        }

        public function clear(): void
        {
        }
    }
}

namespace Darryldecode\Cart\Facades {
    use Darryldecode\Cart\Cart;

    final class CartFacade
    {
        public static function session(?string $sessionKey = null): Cart
        {
            return new Cart();
        }
    }
}
