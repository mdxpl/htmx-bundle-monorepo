<?php

declare(strict_types = 1);

namespace Mdxpl\HtmxBundle\Tests\Response;

use Mdxpl\HtmxBundle\Response\Headers\HtmxResponseHeaderType;
use Mdxpl\HtmxBundle\Response\HtmxRefreshResponse;
use PHPUnit\Framework\TestCase;

class HtmxRefreshResponseTest extends TestCase
{
    public function testResponse(): void
    {
        $response = new HtmxRefreshResponse();
        $this->assertSame(204, $response->responseCode);
        $this->assertSame(HtmxResponseHeaderType::REFRESH, $response->headers->first()->getType());
        $this->assertEquals('true', $response->headers->first()->getValue());
    }
}
