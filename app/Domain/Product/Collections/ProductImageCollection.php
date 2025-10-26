<?php

declare(strict_types=1);

namespace App\Domain\Product\Collections;

use App\Domain\Product\Entities\ProductImage;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, ProductImage>
 */
final class ProductImageCollection implements Countable, IteratorAggregate
{
    /** @var list<ProductImage> */
    private array $items;

    /**
     * @param list<ProductImage> $items
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

    public function first(): ?ProductImage
    {
        return $this->items[0] ?? null;
    }

    /**
     * @return list<ProductImage>
     */
    public function toArray(): array
    {
        return $this->items;
    }
}
