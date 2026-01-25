<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response\Headers;

use Mdxpl\HtmxBundle\Response\Headers\Refresh;
use PHPUnit\Framework\TestCase;

class RefreshTest extends TestCase
{
    public function testRefresh(): void
    {
        $refresh = new Refresh();

        self::assertEquals('HX-Refresh', $refresh->getType()->value);
        self::assertEquals('true', $refresh->getValue());
    }
}
