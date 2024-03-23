<?php

declare(strict_types = 1);

namespace Mdxpl\HtmxBundle\Utils;

use ArrayIterator;
use Traversable;

trait ImmutableCollectionTrait
{
    private array $items = [];

    public function each(callable $callback): static
    {
        $result = [];
        foreach ($this->items as $item) {
            $result[] = $callback($item);
        }

        return new static(...$result);
    }

    public function map(callable $callback): array
    {
        return array_map($callback, $this->items);
    }

    public function filter(callable $callback): self
    {
        return new self(...array_filter($this->items, $callback));
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    private function addItem(mixed $item): static
    {
        return new self(... array_merge($this->items, [$item]));
    }
}
