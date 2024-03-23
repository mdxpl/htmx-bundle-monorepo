<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response;

class HtmxResponseBuilderFactory
{
    public function create(bool $fromHtmxRequest): HtmxResponseBuilder
    {
        return HtmxResponseBuilder::create($fromHtmxRequest);
    }

    public function success(bool $fromHtmxRequest): HtmxResponseBuilder
    {
        return HtmxResponseBuilder::create($fromHtmxRequest)->success();
    }

    public function failure(bool $fromHtmxRequest): HtmxResponseBuilder
    {
        return HtmxResponseBuilder::create($fromHtmxRequest)->failure();
    }
}
