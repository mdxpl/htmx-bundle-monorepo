<?php

namespace Mdxpl\HtmxBundle\Tests\Attribute;

use Mdxpl\HtmxBundle\Attribute\Htmx;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class HtmxTest extends TestCase
{
    public function testHtmxAttribute(): void
    {
        $class = new class() {
            #[Htmx]
            public function test(): true
            {
                return true;
            }
        };

        $reflection = new ReflectionClass($class);
        $attributes = $reflection->getMethod('test')->getAttributes(Htmx::class);

        Assert::assertTrue($class->test());
        Assert::assertCount(1, $attributes);
    }
}
