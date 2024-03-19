<?php

namespace Mdxpl\HtmxBundle\Tests\Response\Headers;

use Mdxpl\HtmxBundle\Response\Headers\Retarget;
use PHPUnit\Framework\TestCase;

class RetargetTest extends TestCase
{
        public function testRetarget(): void
        {
            $retarget = new Retarget('#element');

            $this->assertEquals('#element', $retarget->getValue());
        }
}
