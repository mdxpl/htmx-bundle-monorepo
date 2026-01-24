<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Headers;

/**
 * A CSS selector that updates the target of the content update to a different element on the page
 */
class Retarget implements HtmxResponseHeader
{
    public function __construct(private readonly string $selector)
    {
    }

    public function getType(): HtmxResponseHeaderType
    {
        return HtmxResponseHeaderType::RETARGET;
    }

    public function getValue(): string
    {
        return $this->selector;
    }
}
