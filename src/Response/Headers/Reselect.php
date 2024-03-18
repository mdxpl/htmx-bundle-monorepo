<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Headers;

use Mdxpl\HtmxBundle\Response\Swap\Modifiers\SwapModifier;

/**
 * A CSS selector that allows you to choose which part of the response is used to be swapped in.
 * Overrides an existing hx-select on the triggering element
 */
class Reselect implements HtmxResponseHeader
{
    public function __construct(private readonly string $cssSelector)
    {
    }

    public function getType(): HtmxResponseHeaderType
    {
        return HtmxResponseHeaderType::RESELECT;
    }

    public function getValue(): string
    {
        return $this->cssSelector;
    }
}
