<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Controller;

use Mdxpl\HtmxBundle\Controller\HtmxResponseFacade;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilderFactory;
use Mdxpl\HtmxBundle\Response\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

class HtmxResponseFacadeTest extends TestCase
{
    public function testInitSuccess(): void
    {
        $responseBuilderFactory = $this->createMock(HtmxResponseBuilderFactory::class);
        $responseFactory = $this->createMock(ResponseFactory::class);
        $responseBuilderFactory->expects($this->once())
            ->method('success')
            ->with(true)
            ->willReturn($this->createMock(HtmxResponseBuilder::class));

        (new HtmxResponseFacade($responseBuilderFactory, $responseFactory))
            ->initSuccess(true);
    }

    public function testInitFailure(): void
    {
        $responseBuilderFactory = $this->createMock(HtmxResponseBuilderFactory::class);
        $responseFactory = $this->createMock(ResponseFactory::class);

        $responseBuilderFactory->expects($this->once())
            ->method('failure')
            ->with(true)
            ->willReturn($this->createMock(HtmxResponseBuilder::class));

        (new HtmxResponseFacade($responseBuilderFactory, $responseFactory))
            ->initFailure(true);
    }

    public function testInitResponseBuilder(): void
    {
        $responseBuilderFactory = $this->createMock(HtmxResponseBuilderFactory::class);
        $responseFactory = $this->createMock(ResponseFactory::class);

        $responseBuilderFactory->expects($this->once())
            ->method('create')
            ->with(true)
            ->willReturn($this->createMock(HtmxResponseBuilder::class));

        (new HtmxResponseFacade($responseBuilderFactory, $responseFactory))
            ->initResponseBuilder(true);
    }

    public function testCreateResponse(): void
    {
        $responseBuilderFactory = $this->createMock(HtmxResponseBuilderFactory::class);
        $responseFactory = $this->createMock(ResponseFactory::class);
        $responseBuilder = $this->createMock(HtmxResponseBuilder::class);

        $responseFactory->expects($this->once())
            ->method('create')
            ->with($responseBuilder)
            ->willReturn($this->createMock(Response::class));

        (new HtmxResponseFacade($responseBuilderFactory, $responseFactory))
            ->createResponse($responseBuilder);
    }
}
