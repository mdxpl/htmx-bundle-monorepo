<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response\Swap\Modifiers;

use Mdxpl\HtmxBundle\Response\Swap\Modifiers\IgnoreTitle;
use PHPUnit\Framework\TestCase;

class IgnoreTitleTest extends TestCase
{
    public function testIgnoreTitle(): void
    {
        $ignoreTitle = new IgnoreTitle();

        self::assertEquals('ignoreTitle:true', $ignoreTitle->__toString());
    }
}
