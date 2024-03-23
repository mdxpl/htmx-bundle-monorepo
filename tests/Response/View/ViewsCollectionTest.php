<?php

declare(strict_types = 1);

namespace Mdxpl\HtmxBundle\Tests\Response\View;

use Mdxpl\HtmxBundle\Response\View\View;
use Mdxpl\HtmxBundle\Response\View\ViewsCollection;
use PHPUnit\Framework\TestCase;

class ViewsCollectionTest extends TestCase
{
    public function testAddItem(): void
    {
        $original = new ViewsCollection();
        $actual = $original->add(View::empty())->add(View::empty());

        $this->assertCount(2, $actual);
    }

    public function testIsNoContentIfDoesNotHaveViews(): void
    {
        $original = new ViewsCollection();
        $actual = $original->isNoContent();

        $this->assertTrue($actual);
    }

    public function testIsNoContentIfAllViewsHaveNoTemplate(): void
    {
        $original = new ViewsCollection(... [View::empty(), View::empty()]);
        $actual = $original->isNoContent();

        $this->assertTrue($actual);
    }

    public function testIsNotNoContentIfHasAViewWithTemplate(): void
    {
        $original = new ViewsCollection(... [View::empty(), View::template('template.html.twig')]);
        $actual = $original->isNoContent();

        $this->assertFalse($actual);
    }

    public function testFirst(): void
    {
        $original = new ViewsCollection(... [
            View::template('template.html.twig'),
            View::template('another.html.twig'),
        ]);
        $actual = $original->first();

        $this->assertEquals('template.html.twig', $actual->template);
    }
}
