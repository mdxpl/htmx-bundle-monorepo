<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\DependencyInjection;

use Mdxpl\HtmxBundle\DependencyInjection\HtmxExtension;
use PHPUnit\Framework\TestCase;

class HtmxExtensionTest extends TestCase
{
    public function testHtmxExtension(): void
    {
        $extension = new HtmxExtension();

        $this->assertEquals('mdx_htmx', $extension->getAlias());
    }
}
