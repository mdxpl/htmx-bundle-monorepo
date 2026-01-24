<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Swap\Modifiers;

/**
 * @link https://htmx.org/attributes/hx-swap/
 */
interface SwapModifier
{
    public function __toString(): string;
}
