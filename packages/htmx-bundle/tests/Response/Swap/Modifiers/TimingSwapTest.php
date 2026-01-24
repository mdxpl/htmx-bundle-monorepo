<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response\Swap\Modifiers;

use Mdxpl\HtmxBundle\Response\Swap\Modifiers\TimingSwap;
use PHPUnit\Framework\TestCase;

class TimingSwapTest extends TestCase
{
    public function testTimingSwap(): void
    {
        $timingSwap = new TimingSwap(100);

        $this->assertEquals('swap:100ms', $timingSwap->__toString());
    }
}
