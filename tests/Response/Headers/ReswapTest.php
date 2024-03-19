<?php

namespace Mdxpl\HtmxBundle\Tests\Response\Headers;

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
            new IgnoreTitle()
        );

        $this->assertEquals('outerHTML swap:1000ms ignoreTitle:true', $reswap->getValue());
    }

    public function testReswapWithoutModifiers(): void
    {
        $reswap = new Reswap(
            SwapStyle::AFTER_BEGIN,
        );

        $this->assertEquals('afterbegin', $reswap->getValue());
    }

}
