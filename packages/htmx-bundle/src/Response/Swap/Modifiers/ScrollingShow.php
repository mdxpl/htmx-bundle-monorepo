<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Swap\Modifiers;

/**
 * You can also change the scrolling behavior of the target element
 * @inheritDoc
 */
readonly class ScrollingShow implements SwapModifier
{
    public function __construct(
        private ScrollingDirection $direction,
        private ?string $element = null,
    ) {
    }

    public function __toString(): string
    {
        return implode(':', array_filter(['show', $this->element, $this->direction->value], static fn (?string $v): bool => $v !== null && $v !== ''));
    }
}
