<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Swap\Modifiers;

/**
 * You can modify the time between the swap and the settle logic
 * Default delay: 0ms
 *
 * @inheritDoc
 */
class TimingSettle implements SwapModifier
{
    public function __construct(private readonly int $delayInMilliseconds) { }

    public function __toString(): string
    {
        return sprintf('settle:%dms', $this->delayInMilliseconds);
    }
}