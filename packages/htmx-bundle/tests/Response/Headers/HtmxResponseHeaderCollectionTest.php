<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response\Headers;

use Mdxpl\HtmxBundle\Response\Headers\HtmxResponseHeaderCollection;
use Mdxpl\HtmxBundle\Response\Headers\Redirect;
use Mdxpl\HtmxBundle\Response\Headers\ReplaceUrl;
use PHPUnit\Framework\TestCase;

class HtmxResponseHeaderCollectionTest extends TestCase
{
    public function testAddItem(): void
    {
        $original = new HtmxResponseHeaderCollection();
        $actual = $original
            ->add(new ReplaceUrl('\test'))
            ->add(new Redirect('\\'));

        self::assertCount(2, $actual);
    }

    public function testFirst(): void
    {
        $original = new HtmxResponseHeaderCollection();
        $actual = $original
            ->add(new ReplaceUrl('\test'))
            ->add(new Redirect('\\'));

        self::assertInstanceOf(ReplaceUrl::class, $actual->first());
    }
}
