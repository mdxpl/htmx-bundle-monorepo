<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response\Swap\Modifiers;

use Mdxpl\HtmxBundle\Response\Swap\Modifiers\Transition;
use PHPUnit\Framework\TestCase;

class TransitionTest extends TestCase
{
    public function testTransition(): void
    {
        $transition = new Transition();

        $this->assertEquals('transition:true', $transition->__toString());
    }
}
