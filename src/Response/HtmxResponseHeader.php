<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response;

readonly class HtmxResponseHeader
{
    public function __construct(
        public HtmxResponseHeaderType $type,
        public string $value,
    )
    {
    }
}