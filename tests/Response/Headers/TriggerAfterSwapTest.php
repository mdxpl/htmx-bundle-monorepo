<?php

declare(strict_types = 1);

namespace Mdxpl\HtmxBundle\Tests\Response\Headers;

use Mdxpl\HtmxBundle\Response\Headers\HtmxResponseHeaderType;
use Mdxpl\HtmxBundle\Response\Headers\TriggerAfterSwap;
use PHPUnit\Framework\TestCase;

class TriggerAfterSwapTest extends TestCase
{
    public function testGetType(): void
    {
        $triggerAfterSwap = new TriggerAfterSwap('demo-event');
        $this->assertSame(HtmxResponseHeaderType::TRIGGER_AFTER_SWAP, $triggerAfterSwap->getType());
    }
}
