<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Headers;

/**
 * Pushes a new url into the history stack
 */
class PushUrl implements HtmxResponseHeader
{
    public function __construct(private readonly string $url)
    {
    }

    public function getType(): HtmxResponseHeaderType
    {
        return HtmxResponseHeaderType::PUSH_URL;
    }

    public function getValue(): string
    {
        return $this->url;
    }
}
