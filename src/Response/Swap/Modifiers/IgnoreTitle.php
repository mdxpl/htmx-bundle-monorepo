<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Swap\Modifiers;

/**
 * By default, htmx will update the title of the page if it finds a <title> tag in the response content.
 * You can turn off this behavior.
 * @inheritDoc
 */
class IgnoreTitle implements SwapModifier
{
    public function __toString(): string
    {
        return 'ignoreTitle:true';
    }
}