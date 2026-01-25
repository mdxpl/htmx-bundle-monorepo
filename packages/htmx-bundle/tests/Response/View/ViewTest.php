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

        self::assertTrue($view->hasContent());
        self::assertEquals('template.html.twig', $view->template);
        self::assertNull($view->block);
        self::assertEmpty($view->data);
    }

    public function testCreateWithEmptyTemplate(): void
    {
        $view = View::create('');

        self::assertFalse($view->hasContent());
        self::assertNull($view->template);
        self::assertNull($view->block);
        self::assertEmpty($view->data);
    }

    public function testCreateTemplateWithData(): void
    {
        $view = View::template('template.html.twig', ['test' => 'OK']);

        self::assertTrue($view->hasContent());
        self::assertEquals('template.html.twig', $view->template);
        self::assertArrayHasKey('test', $view->data);
        self::assertContains('OK', $view->data);
    }

    public function testCreateBlock(): void
    {
        $view = View::block('template.html.twig', 'block');

        self::assertTrue($view->hasContent());
        self::assertEquals('template.html.twig', $view->template);
        self::assertEquals('block', $view->block);
        self::assertEmpty($view->data);
    }

    public function testCreateBlockWithData(): void
    {
        $view = View::block('template.html.twig', 'block', ['test' => 'OK']);

        self::assertTrue($view->hasContent());
        self::assertEquals('template.html.twig', $view->template);
        self::assertArrayHasKey('test', $view->data);
        self::assertContains('OK', $view->data);
    }

    public function testCreateBlockWithoutTemplate(): void
    {
        $this->expectException(BlockCannotBeSetWithoutTemplateException::class);

        View::block('', 'block');
    }
}
