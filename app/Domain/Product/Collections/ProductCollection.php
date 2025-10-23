<?php

declare(strict_types=1);

namespace App\Domain\Product\Collections;

use App\Domain\Product\Entities\Product;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, Product>
 */
final class ProductCollection implements IteratorAggregate, Countable
{
    /** @var list<Product> */
    private array $items;

    /**
     * @param list<Product> $items
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
        return count($this->items);
    }

    public function filter(callable $callback): self
    {
        return new self(array_values(array_filter($this->items, $callback)));
    }

    public function slice(int $offset, int $length): self
    {
        return new self(array_slice($this->items, $offset, $length));
    }

    public function first(): ?Product
    {
        return $this->items[0] ?? null;
    }

    /**
     * @return list<Product>
     */
    public function toArray(): array
    {
        return $this->items;
    }
}
