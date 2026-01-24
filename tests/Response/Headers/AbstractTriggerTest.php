<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response\Headers;

use Mdxpl\HtmxBundle\Response\Headers\AbstractTrigger;
use Mdxpl\HtmxBundle\Response\Headers\HtmxResponseHeaderType;
use PHPUnit\Framework\TestCase;

class AbstractTriggerTest extends TestCase
{
    public function testAbstractTrigger(): void
    {
        $abstractTrigger = new class ('event1, event2') extends AbstractTrigger {
            public function getType(): HtmxResponseHeaderType
            {
                return HtmxResponseHeaderType::TRIGGER;
            }
        };

        $this->assertEquals('HX-Trigger', $abstractTrigger->getType()->value);
        $this->assertEquals('event1, event2', $abstractTrigger->getValue());
    }
}
