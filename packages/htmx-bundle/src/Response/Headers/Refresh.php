<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Headers;

/**
 * The client-side will do a full refresh of the page
 */
class Refresh implements HtmxResponseHeader
{
    public function getType(): HtmxResponseHeaderType
    {
        return HtmxResponseHeaderType::REFRESH;
    }

    public function getValue(): string
    {
        return 'true';
    }
}
