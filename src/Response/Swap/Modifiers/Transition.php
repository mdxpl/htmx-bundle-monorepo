<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Swap\Modifiers;

/**
 * Enables View Transitions API
 * @inheritDoc
 */
class Transition implements SwapModifier
{
    public function __toString(): string
    {
        return 'transition:true';
    }
}
