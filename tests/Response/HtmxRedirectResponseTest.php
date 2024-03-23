<?php

declare(strict_types = 1);

namespace Mdxpl\HtmxBundle\Tests\Response;

use Mdxpl\HtmxBundle\Response\Headers\HtmxResponseHeaderType;
use Mdxpl\HtmxBundle\Response\HtmxRedirectResponse;
use PHPUnit\Framework\TestCase;

class HtmxRedirectResponseTest extends TestCase
{
    public function testResponse(): void
    {
        $response = new HtmxRedirectResponse('/path');
        $this->assertSame(204, $response->responseCode);
        $this->assertSame(HtmxResponseHeaderType::REDIRECT, $response->headers->first()->getType());
        $this->assertEquals('/path', $response->headers->first()->getValue());
    }
}
