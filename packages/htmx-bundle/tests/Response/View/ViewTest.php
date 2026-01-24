<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response\View;

use Mdxpl\HtmxBundle\Exception\BlockCannotBeSetWithoutTemplateException;
use Mdxpl\HtmxBundle\Response\View\View;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    public function testCreateEmpty(): void
    {
        $view = View::empty();

        Assert::assertFalse($view->hasContent());
    }

    public function testCreateWithTemplate(): void
    {
        $view = View::template('template.html.twig');

        $this->assertTrue($view->hasContent());
        $this->assertEquals('template.html.twig', $view->template);
        $this->assertNull($view->block);
        $this->assertEmpty($view->data);
    }

    public function testCreateWithEmptyTemplate(): void
    {
        $view = View::create('');

        $this->assertFalse($view->hasContent());
        $this->assertNull($view->template);
        $this->assertNull($view->block);
        $this->assertEmpty($view->data);
    }

    public function testCreateTemplateWithData(): void
    {
        $view = View::template('template.html.twig', ['test' => 'OK']);

        $this->assertTrue($view->hasContent());
        $this->assertEquals('template.html.twig', $view->template);
        $this->assertArrayHasKey('test', $view->data);
        $this->assertContains('OK', $view->data);
    }

    public function testCreateBlock(): void
    {
        $view = View::block('template.html.twig', 'block');

        $this->assertTrue($view->hasContent());
        $this->assertEquals('template.html.twig', $view->template);
        $this->assertEquals('block', $view->block);
        $this->assertEmpty($view->data);
    }

    public function testCreateBlockWithData(): void
    {
        $view = View::block('template.html.twig', 'block', ['test' => 'OK']);

        $this->assertTrue($view->hasContent());
        $this->assertEquals('template.html.twig', $view->template);
        $this->assertArrayHasKey('test', $view->data);
        $this->assertContains('OK', $view->data);
    }

    public function testCreateBlockWithoutTemplate(): void
    {
        $this->expectException(BlockCannotBeSetWithoutTemplateException::class);

        View::block('', 'block');
    }
}
