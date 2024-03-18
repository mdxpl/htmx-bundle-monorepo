<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Headers;

/**
 * Can be used to do a client-side redirect to a new location
 */
class Redirect implements HtmxResponseHeader
{
    public function __construct(private readonly string $url)
    {
    }

    public function getType(): HtmxResponseHeaderType
    {
        return HtmxResponseHeaderType::REDIRECT;
    }

    public function getValue(): string
    {
        return $this->url;
    }
}
