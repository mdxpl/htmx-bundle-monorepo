<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Swap\Modifiers;

/**
 * You can modify the amount of time that htmx will wait after receiving a response to swap the content
 * Default delay: 20ms
 *
 * @inheritDoc
 */
readonly class TimingSwap implements SwapModifier
{
    public function __construct(private int $delayInMilliseconds)
    {
    }

    public function __toString(): string
    {
        return sprintf('swap:%dms', $this->delayInMilliseconds);
    }
}
