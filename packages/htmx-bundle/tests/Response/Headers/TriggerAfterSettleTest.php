<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response\Headers;

use Mdxpl\HtmxBundle\Response\Headers\HtmxResponseHeaderType;
use Mdxpl\HtmxBundle\Response\Headers\TriggerAfterSettle;
use PHPUnit\Framework\TestCase;

class TriggerAfterSettleTest extends TestCase
{
    public function testGetType(): void
    {
        $trigger = new TriggerAfterSettle('demo-event');
        $this->assertSame(HtmxResponseHeaderType::TRIGGER_AFTER_SETTLE, $trigger->getType());
    }
}
