<?php

declare(strict_types = 1);

namespace Mdxpl\HtmxBundle\Response\View;

use Countable;
use IteratorAggregate;
use Mdxpl\HtmxBundle\Utils\ImmutableCollectionTrait;

class ViewsCollection implements Countable, IteratorAggregate
{
    use ImmutableCollectionTrait;

    public function __construct(View ...$items)
    {
        $this->items = $items;
    }

    public function add(View $item): self
    {
        return $this->addItem($item);
    }

    public function isNoContent(): bool
    {
        return empty($this->items) || $this->allViewsHaveNoContent();
    }

    private function allViewsHaveNoContent(): bool
    {
        return $this->filter(fn(View $view) => $view->hasContent())->isEmpty();
    }

    public function first(): View
    {
        return $this->items[0];
    }
}
