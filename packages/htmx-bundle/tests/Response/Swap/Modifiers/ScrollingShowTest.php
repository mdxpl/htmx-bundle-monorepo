<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response\Swap\Modifiers;

use Mdxpl\HtmxBundle\Response\Swap\Modifiers\ScrollingDirection;
use Mdxpl\HtmxBundle\Response\Swap\Modifiers\ScrollingShow;
use PHPUnit\Framework\TestCase;

class ScrollingShowTest extends TestCase
{
    public function testScrollingScroll(): void
    {
        $scrollingShow = new ScrollingShow(ScrollingDirection::TOP);

        self::assertEquals('show:top', $scrollingShow->__toString());
    }

    public function testScrollingScrollWithElement(): void
    {
        $scrollingScroll = new ScrollingShow(ScrollingDirection::BOTTOM, '#element');

        self::assertEquals('show:#element:bottom', $scrollingScroll->__toString());
    }
}
