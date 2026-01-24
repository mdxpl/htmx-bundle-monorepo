<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Headers;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, HtmxResponseHeader>
 */
final class HtmxResponseHeaderCollection implements Countable, IteratorAggregate
{
    /** @var list<HtmxResponseHeader> */
    private array $items = [];

    public function __construct(HtmxResponseHeader ...$items)
    {
        $this->items = array_values($items);
    }

    /**
     * @return Traversable<int, HtmxResponseHeader>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return list<mixed>
     */
    public function map(callable $callback): array
    {
        return array_map($callback, $this->items);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function count(): int
    {
        return \count($this->items);
    }

    public function add(HtmxResponseHeader $header): self
    {
        $new = new self();
        $new->items = [...$this->items, $header];

        return $new;
    }

    public function first(): HtmxResponseHeader
    {
        return $this->items[0];
    }
}
