<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Headers;

/**
 * Replaces the current URL in the location bar
 */
class ReplaceUrl implements HtmxResponseHeader
{
    public function __construct(private readonly string $url)
    {
    }

    public function getType(): HtmxResponseHeaderType
    {
        return HtmxResponseHeaderType::REPLACE_URL;
    }

    public function getValue(): string
    {
        return $this->url;
    }
}
