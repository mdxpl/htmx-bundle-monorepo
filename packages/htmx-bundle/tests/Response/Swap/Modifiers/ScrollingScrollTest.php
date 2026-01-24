<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response\Swap\Modifiers;

use Mdxpl\HtmxBundle\Response\Swap\Modifiers\ScrollingDirection;
use Mdxpl\HtmxBundle\Response\Swap\Modifiers\ScrollingScroll;
use PHPUnit\Framework\TestCase;

class ScrollingScrollTest extends TestCase
{
    public function testScrollingScroll(): void
    {
        $scrollingScroll = new ScrollingScroll(ScrollingDirection::TOP);

        $this->assertEquals('scroll:top', $scrollingScroll->__toString());
    }

    public function testScrollingScrollWithElement(): void
    {
        $scrollingScroll = new ScrollingScroll(ScrollingDirection::BOTTOM, '#element');

        $this->assertEquals('scroll:#element:bottom', $scrollingScroll->__toString());
    }
}
