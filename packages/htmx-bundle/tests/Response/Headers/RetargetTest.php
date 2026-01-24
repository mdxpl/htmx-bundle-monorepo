<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response\Headers;

use Mdxpl\HtmxBundle\Response\Headers\HtmxResponseHeaderType;
use Mdxpl\HtmxBundle\Response\Headers\Retarget;
use PHPUnit\Framework\TestCase;

class RetargetTest extends TestCase
{
    public function testRetarget(): void
    {
        $retarget = new Retarget('#element');

        $this->assertEquals('#element', $retarget->getValue());
    }

    public function testType(): void
    {
        $retarget = new Retarget('#element');

        $this->assertEquals(HtmxResponseHeaderType::RETARGET, $retarget->getType());
    }
}
