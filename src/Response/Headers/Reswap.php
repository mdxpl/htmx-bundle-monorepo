<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Headers;

use Mdxpl\HtmxBundle\Response\Swap\Modifiers\SwapModifier;
use Mdxpl\HtmxBundle\Response\Swap\SwapStyle;

class Reswap implements HtmxResponseHeader
{
    /**
     * @var SwapModifier[]
     */
    private array $modifiers;

    public function __construct(private readonly SwapStyle $style, SwapModifier ...$modifiers)
    {
        $this->modifiers = $modifiers;
    }

    public function getType(): HtmxResponseHeaderType
    {
        return HtmxResponseHeaderType::RESWAP;
    }

    public function getValue(): string
    {
        $modifiers = array_map(fn (SwapModifier $modifier) => (string)$modifier, $this->modifiers);
        $modifiersAsString = implode(' ', $modifiers);

        return implode(' ', array_filter([$this->style->value, $modifiersAsString]));
    }
}
