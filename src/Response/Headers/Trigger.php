<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Headers;

/**
 * Trigger events as soon as the response is received.
 */
final class Trigger extends AbstractTrigger
{
    public function getType(): HtmxResponseHeaderType
    {
        return HtmxResponseHeaderType::TRIGGER;
    }
}
