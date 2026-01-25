<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response;

use Mdxpl\HtmxBundle\Response\Headers\HtmxResponseHeaderType;
use Mdxpl\HtmxBundle\Response\HtmxRefreshResponse;
use PHPUnit\Framework\TestCase;

class HtmxRefreshResponseTest extends TestCase
{
    public function testResponse(): void
    {
        $response = new HtmxRefreshResponse();
        self::assertSame(204, $response->responseCode);
        self::assertSame(HtmxResponseHeaderType::REFRESH, $response->headers->first()->getType());
        self::assertEquals('true', $response->headers->first()->getValue());
    }
}
