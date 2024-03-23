<?php

namespace Mdxpl\HtmxBundle\Tests\Attribute;

use Mdxpl\HtmxBundle\Attribute\HtmxOnly;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class HtmxTest extends TestCase
{
    public function testHtmxAttribute(): void
    {
        $class = new class () {
            #[HtmxOnly]
            public function test(): true
            {
                return true;
            }
        };

        $reflection = new ReflectionClass($class);
        $attributes = $reflection->getMethod('test')->getAttributes(HtmxOnly::class);

        Assert::assertTrue($class->test());
        Assert::assertCount(1, $attributes);
    }
}
