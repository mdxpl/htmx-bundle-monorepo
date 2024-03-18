<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Headers;

/**
 * Trigger events after the settling step.
 */
final class TriggerAfterSettle extends AbstractTrigger
{
    public function getType(): HtmxResponseHeaderType
    {
        return HtmxResponseHeaderType::TRIGGER_AFTER_SETTLE;
    }
}
