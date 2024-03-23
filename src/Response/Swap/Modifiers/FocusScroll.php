<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Swap\Modifiers;

/**
 * You can modify the scroll to focus behavior
 * @inheritDoc
 */
readonly class FocusScroll implements SwapModifier
{
    public function __construct(private bool $boolValue)
    {
    }

    public function __toString(): string
    {
        $boolAsString = $this->boolValue ? 'true' : 'false';

        return sprintf('focus-scroll:%s', $boolAsString);
    }
}
