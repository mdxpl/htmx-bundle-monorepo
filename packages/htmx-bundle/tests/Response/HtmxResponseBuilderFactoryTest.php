<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response;

use Mdxpl\HtmxBundle\Response\HtmxResponseBuilderFactory;
use PHPUnit\Framework\TestCase;

class HtmxResponseBuilderFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $builder = new HtmxResponseBuilderFactory()->create(true);
        $htmxResponse = $builder->build();

        $this->assertCount(0, $htmxResponse->views);
        $this->assertCount(0, $htmxResponse->headers);
        $this->assertEquals(204, $htmxResponse->responseCode);
    }

    public function testCreateWithView(): void
    {
        $builder = new HtmxResponseBuilderFactory()->create(true)->view('view');
        $htmxResponse = $builder->build();

        $this->assertCount(1, $htmxResponse->views);
        $this->assertCount(0, $htmxResponse->headers);
        $this->assertEquals(204, $htmxResponse->responseCode);
    }

    public function testSuccess(): void
    {
        $builder = new HtmxResponseBuilderFactory()->success(true);
        $htmxResponse = $builder->build();

        $this->assertCount(0, $htmxResponse->views);
        $this->assertCount(0, $htmxResponse->headers);
        $this->assertEquals(200, $htmxResponse->responseCode);
    }

    public function testFailure(): void
    {
        $builder = new HtmxResponseBuilderFactory()->failure(true);
        $htmxResponse = $builder->build();

        $this->assertCount(0, $htmxResponse->views);
        $this->assertCount(0, $htmxResponse->headers);
        $this->assertEquals(422, $htmxResponse->responseCode);
    }
}
