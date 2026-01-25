<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response\Headers;

use Mdxpl\HtmxBundle\Response\Headers\HtmxResponseHeaderType;
use Mdxpl\HtmxBundle\Response\Headers\Reswap;
use Mdxpl\HtmxBundle\Response\Swap\Modifiers\IgnoreTitle;
use Mdxpl\HtmxBundle\Response\Swap\Modifiers\TimingSwap;
use Mdxpl\HtmxBundle\Response\Swap\SwapStyle;
use PHPUnit\Framework\TestCase;

class ReswapTest extends TestCase
{
    public function testReswap(): void
    {
        $reswap = new Reswap(
            SwapStyle::OUTER_HTML,
            new TimingSwap(1000),
            new IgnoreTitle(),
        );

        self::assertEquals('outerHTML swap:1000ms ignoreTitle:true', $reswap->getValue());
    }

    public function testType(): void
    {
        $reswap = new Reswap(SwapStyle::AFTER_BEGIN);

        self::assertEquals(HtmxResponseHeaderType::RESWAP, $reswap->getType());
    }

    public function testReswapWithoutModifiers(): void
    {
        $reswap = new Reswap(
            SwapStyle::AFTER_BEGIN,
        );

        self::assertEquals('afterbegin', $reswap->getValue());
    }

    public function testTypeWithModifiers(): void
    {
        $reswap = new Reswap(
            SwapStyle::AFTER_BEGIN,
            new TimingSwap(1000),
            new IgnoreTitle(),
        );

        self::assertEquals('afterbegin swap:1000ms ignoreTitle:true', $reswap->getValue());
    }
}
