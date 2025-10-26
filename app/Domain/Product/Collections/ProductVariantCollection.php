<?php

declare(strict_types=1);

namespace App\Domain\Product\Collections;

use App\Domain\Product\Entities\ProductVariant;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, ProductVariant>
 */
final class ProductVariantCollection implements Countable, IteratorAggregate
{
    /** @var list<ProductVariant> */
    private array $items;

    /**
     * @param list<ProductVariant> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = array_values($items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return is_countable($this->items) ? count($this->items) : 0;
    }

    /**
     * @return list<ProductVariant>
     */
    public function toArray(): array
    {
        return $this->items;
    }
}
