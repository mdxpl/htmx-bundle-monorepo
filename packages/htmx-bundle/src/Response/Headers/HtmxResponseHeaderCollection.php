<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Headers;

use Countable;
use IteratorAggregate;
use Mdxpl\HtmxBundle\Utils\ImmutableCollectionTrait;

class HtmxResponseHeaderCollection implements Countable, IteratorAggregate
{
    use ImmutableCollectionTrait;

    public function __construct(HtmxResponseHeader ...$items)
    {
        $this->items = $items;
    }

    public function add(HtmxResponseHeader $header): self
    {
        return $this->addItem($header);
    }

    public function first(): HtmxResponseHeader
    {
        return $this->items[0];
    }
}
