<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response;

use Mdxpl\HtmxBundle\Response\Headers\HtmxResponseHeaderCollection;
use Mdxpl\HtmxBundle\Response\Headers\Trigger;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\View\View;
use Mdxpl\HtmxBundle\Response\View\ViewsCollection;
use PHPUnit\Framework\TestCase;

class HtmxResponseTest extends TestCase
{
    public function testCreateWithViewCollection(): void
    {
        $htmxResponse = new HtmxResponse(
            200,
            new ViewsCollection(
                View::template('template.html.twig'),
                View::template('template.html.twig'),
            ),
        );

        $this->assertEquals('template.html.twig', $htmxResponse->views->first()->template);
        $this->assertCount(2, $htmxResponse->views);
    }

    public function testCreateWithView(): void
    {
        $htmxResponse = new HtmxResponse(
            200,
            View::template('template.html.twig'),
        );

        $this->assertEquals('template.html.twig', $htmxResponse->views->first()->template);
        $this->assertCount(1, $htmxResponse->views);
    }


    public function testCreateWithNoContent(): void
    {
        $htmxResponse = new HtmxResponse();

        $this->assertEquals(204, $htmxResponse->responseCode);
        $this->assertCount(0, $htmxResponse->views);
        $this->assertCount(0, $htmxResponse->headers);
    }

    public function testCreateWithResponseCode(): void
    {
        $htmxResponse = new HtmxResponse(201);

        $this->assertEquals(201, $htmxResponse->responseCode);
    }

    public function testCreateWithHeader(): void
    {
        $htmxResponse = new HtmxResponse(
            200,
            null,
            new HtmxResponseHeaderCollection(
                new Trigger('demo-event'),
            ),
        );

        $this->assertCount(1, $htmxResponse->headers);
        $this->assertInstanceOf(Trigger::class, $htmxResponse->headers->first());
    }

    public function testCreateWithHeaders(): void
    {
        $htmxResponse = new HtmxResponse(
            200,
            null,
            new HtmxResponseHeaderCollection(
                new Trigger('demo1-event'),
                new Trigger('demo-event'),
            ),
        );

        $this->assertCount(2, $htmxResponse->headers);
        $this->assertInstanceOf(Trigger::class, $htmxResponse->headers->first());
    }

    public function testCreateWithNoContentAndHeaders(): void
    {
        $htmxResponse = new HtmxResponse(
            204,
            null,
            new HtmxResponseHeaderCollection(
                new Trigger('demo1-event'),
                new Trigger('demo-event'),
            ),
        );

        $this->assertEquals(204, $htmxResponse->responseCode);
        $this->assertCount(2, $htmxResponse->headers);
        $this->assertInstanceOf(Trigger::class, $htmxResponse->headers->first());
    }
}
