<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response;

use Mdxpl\HtmxBundle\Response\Headers\HtmxResponseHeader;
use Mdxpl\HtmxBundle\Response\Headers\HtmxResponseHeaderCollection;
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
use Mdxpl\HtmxBundle\Response\View\View;
use Mdxpl\HtmxBundle\Response\View\ViewsCollection;

class HtmxResponseBuilder
{
    use HasDefaultViewDataTrait;

    private HtmxResponseHeaderCollection $headers;

    private ViewsCollection $views;

    private int $responseCode = 204;

    /**
     * @param bool $fromHtmxRequest Whether the request is from htmx or not.
     * @param array $commonViewData View data that will be added to each view.
     * @param bool $setDefaultViewData Whether build-in view params should be added to each view.
     */
    private function __construct(
        private readonly bool $fromHtmxRequest,
        private readonly array $commonViewData = [],
        private readonly bool $setDefaultViewData = true,
    ) {
        $this->views = new ViewsCollection();
        $this->headers = new HtmxResponseHeaderCollection();
        $this->setDefaultViewData(Result::UNKNOWN, $fromHtmxRequest);
    }

    /**
     * @param bool $fromHtmxRequest Whether the request is from htmx or not.
     */
    public static function create(bool $fromHtmxRequest, array $viewData = []): self
    {
        return new self($fromHtmxRequest, $viewData);
    }

    /**
     * Creates a builder with explicit configuration for default view data.
     * Used internally by HtmxResponseBuilderFactory to respect bundle configuration.
     */
    public static function createWithConfig(bool $fromHtmxRequest, array $viewData, bool $setDefaultViewData): self
    {
        return new self($fromHtmxRequest, $viewData, $setDefaultViewData);
    }

    public function responseCode(int $responseCode, Result $result): self
    {
        $this->withResponseCode($responseCode);
        $this->defaultViewData[View::RESULT_VIEW_PARAM_NAME] = $result;

        return $this;
    }

    public function noContent(Result $result = Result::UNKNOWN): self
    {
        $this->clearViews();
        $this->withResponseCode(204);
        $this->defaultViewData[View::RESULT_VIEW_PARAM_NAME] = $result;

        return $this;
    }

    public function failure(): self
    {
        $this->withResponseCode(422);
        $this->defaultViewData[View::RESULT_VIEW_PARAM_NAME] = Result::FAILURE;

        return $this;
    }

    public function success(): self
    {
        $this->withResponseCode(200);
        $this->defaultViewData[View::RESULT_VIEW_PARAM_NAME] = Result::SUCCESS;

        return $this;
    }

    /**
     * Adds a single header.
     */
    public function header(HtmxResponseHeader $header): self
    {
        $this->headers = $this->headers->add($header);

        return $this;
    }

    /**
     * Adds a single view param.
     */
    public function view(string $template, array $viewData = [], ?string $block = null): self
    {
        $viewData = array_merge($this->commonViewData, $viewData);
        if ($this->setDefaultViewData) {
            $this->assertNotOverridesReservedViewParams($viewData);
            $viewData = array_merge($this->defaultViewData, $viewData);
        }

        $this->views = $this->views->add(View::create($template, $block, $viewData));

        return $this;
    }

    public function viewBlock(string $template, string $block, array $viewData = []): self
    {
        return $this->view($template, $viewData, $block);
    }

    /**
     * Clears all views. Use withView() to add a single view.
     */
    public function clearViews(): self
    {
        $this->views = new ViewsCollection();

        return $this;
    }

    /**
     * Allows you to do a client-side redirect that does not do a full page reload
     */
    public function location(string $url): self
    {
        $this->header(new Location($url));

        return $this;
    }

    /**
     * Pushes a new url into the history stack
     */
    public function pushUrl(string $url): self
    {
        $this->header(new PushUrl($url));

        return $this;
    }

    /**
     * Can be used to do a client-side redirect to a new location
     */
    public function redirect(string $url): self
    {
        $this->header(new Redirect($url));

        return $this;
    }

    /**
     * The client-side will do a full refresh of the page
     */
    public function refresh(): self
    {
        $this->header(new Refresh());

        return $this;
    }

    /**
     * Replaces the current URL in the location bar
     */
    public function replaceUrl(string $url): self
    {
        $this->header(new ReplaceUrl($url));

        return $this;
    }

    /**
     * Allows you to specify how the response will be swapped.
     * @link https://htmx.org/attributes/hx-swap/
     */
    public function withReswap(SwapStyle $style, SwapModifier ...$modifiers): self
    {
        $this->header(new Reswap($style, ...$modifiers));

        return $this;
    }

    /**
     * A CSS selector that updates the target of the content update to a different element on the page
     */
    public function retarget(string $cssSelector): self
    {
        $this->header(new Retarget($cssSelector));

        return $this;
    }

    /**
     * A CSS selector that allows you to choose which part of the response is used to be swapped in.
     * Overrides an existing hx-select on the triggering element
     */
    public function reselect(string $cssSelector): self
    {
        $this->header(new Reselect($cssSelector));

        return $this;
    }

    /**
     * Trigger events as soon as the response is received.
     * @see \Mdxpl\HtmxBundle\Response\Headers\AbstractTrigger
     */
    public function trigger(string|array $events): self
    {
        $this->header(new Trigger($events));

        return $this;
    }

    /**
     * Trigger events after the settling step.
     * @see \Mdxpl\HtmxBundle\Response\Headers\AbstractTrigger
     */
    public function triggerAfterSettle(string|array $events): self
    {
        $this->header(new TriggerAfterSettle($events));

        return $this;
    }

    /**
     * Allows you to trigger client-side events after the swap step.
     * @see \Mdxpl\HtmxBundle\Response\Headers\AbstractTrigger
     */
    public function triggerAfterSwap(string|array $events): self
    {
        $this->header(new TriggerAfterSwap($events));

        return $this;
    }

    public function build(): HtmxResponse
    {
        return new HtmxResponse(
            $this->responseCode,
            $this->views,
            $this->headers,
        );
    }

    private function withResponseCode(int $responseCode): self
    {
        $this->responseCode = $responseCode;

        return $this;
    }
}
