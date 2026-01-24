<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response\Swap\Modifiers;

use Mdxpl\HtmxBundle\Response\Swap\Modifiers\FocusScroll;
use PHPUnit\Framework\TestCase;

class FocusScrollTest extends TestCase
{
    public function testFocusScroll(): void
    {
        $focusScroll = new FocusScroll(true);

        $this->assertEquals('focus-scroll:true', $focusScroll->__toString());
    }
}
