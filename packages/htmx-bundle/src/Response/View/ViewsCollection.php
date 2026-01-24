<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\View;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, View>
 */
final class ViewsCollection implements Countable, IteratorAggregate
{
    /** @var list<View> */
    private array $items = [];

    public function __construct(View ...$items)
    {
        $this->items = array_values($items);
    }

    /**
     * @return Traversable<int, View>
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

    public function add(View $item): self
    {
        $new = new self();
        $new->items = [...$this->items, $item];

        return $new;
    }

    public function isNoContent(): bool
    {
        return $this->items === [] || $this->allViewsHaveNoContent();
    }

    private function allViewsHaveNoContent(): bool
    {
        $withContent = array_filter($this->items, static fn (View $view): bool => $view->hasContent());

        return $withContent === [];
    }

    public function first(): View
    {
        return $this->items[0];
    }
}
