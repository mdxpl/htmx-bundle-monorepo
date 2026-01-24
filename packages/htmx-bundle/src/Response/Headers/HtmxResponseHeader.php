<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Headers;

interface HtmxResponseHeader
{
    public function getType(): HtmxResponseHeaderType;

    public function getValue(): string;
}
