<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response;

use Mdxpl\HtmxBundle\Exception\ReservedViewParamCannotBeOverriddenException;
use Mdxpl\HtmxBundle\Exception\ResponseCodeNotSetException;
use Mdxpl\HtmxBundle\Response\Headers\HtmxResponseHeader;
use Mdxpl\HtmxBundle\Response\Headers\Location;
use Mdxpl\HtmxBundle\Response\Headers\PushUrl;
use Mdxpl\HtmxBundle\Response\Headers\Redirect;
use Mdxpl\HtmxBundle\Response\Headers\Refresh;
use Mdxpl\HtmxBundle\Response\Headers\ReplaceUrl;
use Mdxpl\HtmxBundle\Response\Headers\Reselect;
use Mdxpl\HtmxBundle\Response\Headers\Reswap;
use Mdxpl\HtmxBundle\Response\Headers\Retarget;
use Mdxpl\HtmxBundle\Response\Headers\Trigger;
use Mdxpl\HtmxBundle\Response\Headers\TriggerAfterSettle;
use Mdxpl\HtmxBundle\Response\Headers\TriggerAfterSwap;
use Mdxpl\HtmxBundle\Response\Swap\Modifiers\SwapModifier;
use Mdxpl\HtmxBundle\Response\Swap\SwapStyle;

class HtmxResponseBuilder
{
    /**
     * The build-in view params that are set automatically and cannot be overridden.
     */
    public const RESERVED_VIEW_PARAMS = [
        HtmxResponse::RESULT_VIEW_PARAM_NAME,
        HtmxResponse::IS_HTMX_REQUEST_VIEW_PARAM_NAME,
    ];

    private array $viewParams = [];

    private array $headers = [];

    private int $responseCode = 200;

    private ?string $template = null;

    private ?string $block = null;

    /**
     * @param bool $fromHtmxRequest Whether the request is from htmx or not.
     */
    private function __construct(public readonly bool $fromHtmxRequest)
    {
        $this->viewParams[HtmxResponse::RESULT_VIEW_PARAM_NAME] = Result::UNKNOWN;
        $this->viewParams[HtmxResponse::IS_HTMX_REQUEST_VIEW_PARAM_NAME] = $fromHtmxRequest;
    }

    /**
     * @param bool $fromHtmxRequest Whether the request is from htmx or not.
     */
    public static function create(bool $fromHtmxRequest): self
    {
        return new self($fromHtmxRequest);
    }

    /**
     * The template is the path to the Twig template that should be rendered.
     */
    public function withTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    /**
     * The block is the name of the block that should be rendered from the template.
     *
     * If null, the entire template will be rendered.
     * The template must contain the block, otherwise an exception will be thrown.
     */
    public function withBlock(?string $block): self
    {
        $this->block = $block;

        return $this;
    }

    /**
     * Consider using withSuccess() or withFailure() if you want to set the response code to a common value.
     */
    public function withResponseCode(int $responseCode): self
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    /**
     * Sets the response code to 422. Which is the common response code for validation errors.
     */
    public function withFailure(): self
    {
        $this->withResponseCode(422);
        $this->viewParams[HtmxResponse::RESULT_VIEW_PARAM_NAME] = Result::FAILURE;

        return $this;
    }

    /**
     * Sets the response code to 200. Which is the common response code for successful requests.
     */
    public function withSuccess(): self
    {
        $this->withResponseCode(200);
        $this->viewParams[HtmxResponse::RESULT_VIEW_PARAM_NAME] = Result::SUCCESS;

        return $this;
    }

    /**
     * Overwrites all headers. Use withHeader() to add a single header.
     */
    public function withHeaders(HtmxResponseHeader ...$headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Adds a single header.
     */
    public function withHeader(HtmxResponseHeader $header): self
    {
        $this->headers[$header->getType()->value] = $header;

        return $this;
    }

    /**
     * Overwrites all view params. Use withViewParam() to add a single param.
     */
    public function withViewParams(array $viewParams): self
    {
        $this->assertNotOverridesReservedViewParams(...array_keys($viewParams));
        $this->viewParams = array_merge($this->getReservedViewParams(), $viewParams);

        return $this;
    }

    /**
     * Adds a single view param.
     */
    public function withViewParam(string $name, mixed $param): self
    {
        $this->assertNotOverridesReservedViewParams($name);
        $this->viewParams[$name] = $param;

        return $this;
    }

    /**
     * Allows you to do a client-side redirect that does not do a full page reload
     */
    public function withLocation(string $url): self
    {
        $this->withHeader(new Location($url));

        return $this;
    }

    /**
     * Pushes a new url into the history stack
     */
    public function withPushUrl(string $url): self
    {
        $this->withHeader(new PushUrl($url));

        return $this;
    }

    /**
     * Can be used to do a client-side redirect to a new location
     */
    public function withRedirect(string $url): self
    {
        $this->withHeader(new Redirect($url));

        return $this;
    }

    /**
     * The client-side will do a full refresh of the page
     */
    public function withRefresh(): self
    {
        $this->withHeader(new Refresh());

        return $this;
    }

    /**
     * Replaces the current URL in the location bar
     */
    public function withReplaceUrl(string $url): self
    {
        $this->withHeader(new ReplaceUrl($url));

        return $this;
    }

    /**
     * Allows you to specify how the response will be swapped.
     * @link https://htmx.org/attributes/hx-swap/
     */
    public function withReswap(SwapStyle $style, SwapModifier ...$modifiers): self
    {
        $this->withHeader(new Reswap($style, ...$modifiers));

        return $this;
    }

    /**
     * A CSS selector that updates the target of the content update to a different element on the page
     */
    public function withRetarget(string $cssSelector): self
    {
        $this->withHeader(new Retarget($cssSelector));

        return $this;
    }

    /**
     * A CSS selector that allows you to choose which part of the response is used to be swapped in.
     * Overrides an existing hx-select on the triggering element
     */
    public function withReselect(string $cssSelector): self
    {
        $this->withHeader(new Reselect($cssSelector));

        return $this;
    }

    /**
     * Trigger events as soon as the response is received.
     * @see \Mdxpl\HtmxBundle\Response\Headers\AbstractTrigger
     */
    public function withTrigger(string|array $events): self
    {
        $this->withHeader(new Trigger($events));

        return $this;
    }

    /**
     * Trigger events after the settling step.
     * @see \Mdxpl\HtmxBundle\Response\Headers\AbstractTrigger
     */
    public function withTriggerAfterSettle(string|array $events): self
    {
        $this->withHeader(new TriggerAfterSettle($events));

        return $this;
    }

    /**
     * Allows you to trigger client-side events after the swap step.
     * @see \Mdxpl\HtmxBundle\Response\Headers\AbstractTrigger
     */
    public function withTriggerAfterSwap(string|array $events): self
    {
        $this->withHeader(new TriggerAfterSwap($events));

        return $this;
    }


    public function build(): HtmxResponse
    {
        $this->assertResponseCodeIsSet();

        return new HtmxResponse(
            $this->template,
            $this->block,
            $this->viewParams,
            $this->responseCode,
            $this->headers,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getReservedViewParams(): array
    {
        return array_filter(
            $this->viewParams,
            fn(string $key) => in_array($key, self::RESERVED_VIEW_PARAMS, true),
            ARRAY_FILTER_USE_KEY,
        );
    }

    private function assertNotOverridesReservedViewParams(mixed ...$params): void
    {
        foreach (HtmxResponseBuilder::RESERVED_VIEW_PARAMS as $reservedViewParam) {
            if (in_array($reservedViewParam, $params, true)) {
                throw ReservedViewParamCannotBeOverriddenException::withViewParamName($reservedViewParam);
            }
        }
    }

    private function assertResponseCodeIsSet(): void
    {
        if ($this->responseCode === null) {
            throw ResponseCodeNotSetException::create();
        }
    }
}