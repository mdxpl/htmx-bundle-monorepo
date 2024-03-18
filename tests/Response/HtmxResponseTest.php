<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response;

use Mdxpl\HtmxBundle\Response\HtmxResponse;
use PHPUnit\Framework\TestCase;

class HtmxResponseTest extends TestCase
{
    public function testCreate(): void
    {
        $htmxResponse = new HtmxResponse(
            'template',
            'block',
            ['test' => 'ok'],
            201
        );

        $this->assertEquals('template', $htmxResponse->template);
        $this->assertEquals('block', $htmxResponse->blockName);
        $this->assertArrayHasKey('test', $htmxResponse->viewParams);
        $this->assertContains('ok', $htmxResponse->viewParams);
        $this->assertEquals(201, $htmxResponse->responseCode);
    }
}
