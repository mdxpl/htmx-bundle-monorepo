<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response\Swap\Modifiers;

use Mdxpl\HtmxBundle\Response\Swap\Modifiers\TimingSettle;
use PHPUnit\Framework\TestCase;

class TimingSettleTest extends TestCase
{
    public function testTimingSettle(): void
    {
        $timingSettle = new TimingSettle(100);

        $this->assertEquals('settle:100ms', $timingSettle->__toString());
    }
}
