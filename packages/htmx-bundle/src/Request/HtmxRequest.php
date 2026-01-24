<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * Represents an htmx request with all its headers parsed into typed properties.
 *
 * htmx sends various headers with each request to provide context about the request.
 * This class makes these headers easily accessible in your Symfony controllers.
 *
 * Can be injected directly into controller actions via argument resolver.
 *
 * @example Controller usage:
 *     #[Route('/api/items')]
 *     public function list(HtmxRequest $htmx): Response
 *     {
 *         if ($htmx->isHtmx) {
 *             // Return partial HTML for htmx request
 *             return $this->render('items/_list.html.twig');
 *         }
 *         // Return full page for regular request
 *         return $this->render('items/list.html.twig');
 *     }
 *
 * @link https://htmx.org/reference/#request_headers
 */
readonly class HtmxRequest
{
    public const string REQUEST_ATTRIBUTE_NAME = 'mdx_htmx_request';

    /**
     * @param bool $isHtmx True if the request was made by htmx (HX-Request header).
     *                     Use this to determine if you should return partial or full HTML.
     *                     @link https://htmx.org/reference/#request_headers
     *
     * @param bool $isBoosted True if the request is via an element with hx-boost attribute (HX-Boosted header).
     *                        Boosted links/forms behave like htmx requests but expect full page responses.
     *                        @link https://htmx.org/attributes/hx-boost/
     *
     * @param bool|null $isForHistoryRestoration True if the request is for history restoration after a miss
     *                                           in the local history cache (HX-History-Restore-Request header).
     *                                           @link https://htmx.org/docs/#history
     *
     * @param string|null $currentUrl The current URL of the browser when the request was made (HX-Current-URL header).
     *                                Useful for conditional logic based on the page the user is on.
     *
     * @param string|null $prompt The user response to an hx-prompt attribute (HX-Prompt header).
     *                            Contains the text entered by the user in the prompt dialog.
     *                            @link https://htmx.org/attributes/hx-prompt/
     *
     * @param string|null $target The id of the target element if it exists (HX-Target header).
     *                            This is the element that will receive the response content.
     *                            @link https://htmx.org/attributes/hx-target/
     *
     * @param string|null $triggerName The name of the triggered element if it exists (HX-Trigger-Name header).
     *                                 Useful for forms with multiple submit buttons.
     *
     * @param string|null $trigger The id of the triggered element if it exists (HX-Trigger header).
     *                             This is the element that initiated the request (clicked button, changed input, etc.).
     */
    public function __construct(
        public bool $isHtmx = false,
        public bool $isBoosted = false,
        public ?bool $isForHistoryRestoration = false,
        public ?string $currentUrl = null,
        public ?string $prompt = null,
        public ?string $target = null,
        public ?string $triggerName = null,
        public ?string $trigger = null,
    ) {
    }

    /**
     * Creates an HtmxRequest from a Symfony HttpFoundation Request.
     *
     * Automatically extracts all htmx headers from the request.
     * This is called internally by the argument resolver.
     *
     * @param Request $httpRequest The Symfony HTTP request
     *
     * @return self Populated HtmxRequest instance
     *
     * @internal Used by HtmxRequestArgumentValueResolver
     */
    public static function createFromSymfonyHttpRequest(Request $httpRequest): self
    {
        $headers = $httpRequest->headers;

        return new self(
            isHtmx: (bool) $headers->get(HtmxRequestHeaderType::REQUEST->value),
            isBoosted: (bool) $headers->get(HtmxRequestHeaderType::BOOSTED->value),
            isForHistoryRestoration: (bool) $headers->get(HtmxRequestHeaderType::HISTORY_RESTORE_REQUEST->value),
            currentUrl: $headers->get(HtmxRequestHeaderType::CURRENT_URL->value),
            prompt: $headers->get(HtmxRequestHeaderType::PROMPT->value),
            target: $headers->get(HtmxRequestHeaderType::TARGET->value),
            triggerName: $headers->get(HtmxRequestHeaderType::TRIGGER_NAME->value),
            trigger: $headers->get(HtmxRequestHeaderType::TRIGGER->value),
        );
    }
}
