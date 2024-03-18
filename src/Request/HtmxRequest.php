<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Request;

use Symfony\Component\HttpFoundation\Request;

readonly class HtmxRequest
{
    public function __construct(
        public bool $isHtmx = false,
        public bool $isBoosted = false,
        public ?bool $isForHistoryRestoration = false,
        public ?string $currentUrl = null,
        public ?string $prompt = null,
        public ?string $target = null,
        public ?string $triggerName = null,
        public ?string $trigger = null,
    )
    {
    }

    public static function createFromSymfonyHttpRequest(Request $httpRequest): self
    {
        $headers = $httpRequest->headers;

        return new self(
            isHtmx: (bool)$headers->get(HtmxRequestHeaderType::REQUEST->value),
            isBoosted: (bool)$headers->get(HtmxRequestHeaderType::BOOSTED->value),
            isForHistoryRestoration: (bool)$headers->get(HtmxRequestHeaderType::HISTORY_RESTORE_REQUEST->value),
            currentUrl: $headers->get(HtmxRequestHeaderType::CURRENT_URL->value),
            prompt: $headers->get(HtmxRequestHeaderType::PROMPT->value),
            target: $headers->get(HtmxRequestHeaderType::TARGET->value),
            triggerName: $headers->get(HtmxRequestHeaderType::TRIGGER_NAME->value),
            trigger: $headers->get(HtmxRequestHeaderType::TRIGGER->value),
        );
    }
}