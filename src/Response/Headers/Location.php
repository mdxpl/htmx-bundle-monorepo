<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Headers;

class Location implements HtmxResponseHeader
{
    public function __construct(private readonly string $url)
    {
    }

    public function getType(): HtmxResponseHeaderType
    {
        return HtmxResponseHeaderType::LOCATION;
    }

    public function getValue(): string
    {
        return $this->url;
    }
}
