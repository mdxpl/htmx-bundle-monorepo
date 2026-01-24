<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response;

/**
 * Factory for creating HtmxResponseBuilder instances with configuration.
 *
 * Use this factory when you need to respect bundle configuration for default view data.
 * For simple cases, you can use HtmxResponseBuilder::create() directly.
 */
class HtmxResponseBuilderFactory
{
    public function __construct(
        private readonly bool $setDefaultViewData = true,
    ) {
    }

    public function create(bool $fromHtmxRequest, array $viewData = []): HtmxResponseBuilder
    {
        return HtmxResponseBuilder::createWithConfig($fromHtmxRequest, $viewData, $this->setDefaultViewData);
    }

    public function success(bool $fromHtmxRequest, array $viewData = []): HtmxResponseBuilder
    {
        return $this->create($fromHtmxRequest, $viewData)->success();
    }

    public function failure(bool $fromHtmxRequest, array $viewData = []): HtmxResponseBuilder
    {
        return $this->create($fromHtmxRequest, $viewData)->failure();
    }
}
