<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response;

use Mdxpl\HtmxBundle\Response\Result;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    public function testSuccess(): void
    {
        $result = Result::SUCCESS;

        self::assertTrue($result->isSuccess());
        self::assertFalse($result->isFailure());
    }

    public function testFailure(): void
    {
        $result = Result::FAILURE;

        self::assertFalse($result->isSuccess());
        self::assertTrue($result->isFailure());
    }

    public function testUnknown(): void
    {
        $result = Result::UNKNOWN;

        self::assertFalse($result->isSuccess());
        self::assertFalse($result->isFailure());
    }
}
