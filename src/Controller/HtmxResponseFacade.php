<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Controller;

use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilderFactory;
use Mdxpl\HtmxBundle\Response\ResponseFactory;
use Symfony\Component\HttpFoundation\Response;

readonly class HtmxResponseFacade
{
    public function __construct(
        private HtmxResponseBuilderFactory $responseBuilderFactory,
        private ResponseFactory $responseFactory,
    )
    {
    }

    public function initSuccess(bool $fromHtmxRequest): HtmxResponseBuilder
    {
        return $this->responseBuilderFactory->success($fromHtmxRequest);
    }

    public function initFailure(bool $fromHtmxRequest): HtmxResponseBuilder
    {
        return $this->responseBuilderFactory->failure($fromHtmxRequest);
    }

    public function initResponseBuilder(bool $fromHtmxRequest): HtmxResponseBuilder
    {
        return $this->responseBuilderFactory->create($fromHtmxRequest);
    }

    /**
     * If you return the HtmxResponse from your controller, the HtmxResponseSubscriber will handle it.
     * Use it if you want to create the Symfony response manually.
     */
    public function createResponse(HtmxResponseBuilder $responseBuilder): Response
    {
        return $this->responseFactory->create($responseBuilder->build());
    }
}