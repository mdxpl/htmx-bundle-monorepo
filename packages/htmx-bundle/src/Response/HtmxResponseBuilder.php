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

/**
 * Fluent builder for creating htmx responses.
 *
 * Provides a convenient API for setting HTTP status codes, rendering views/blocks,
 * and configuring htmx response headers.
 *
 * @example Basic usage:
 *     return HtmxResponseBuilder::create($htmx->isHtmx)
 *         ->success()
 *         ->view('template.html.twig', ['data' => $data])
 *         ->build();
 *
 * @example With htmx headers:
 *     return HtmxResponseBuilder::create($htmx->isHtmx)
 *         ->success()
 *         ->view('template.html.twig')
 *         ->trigger(['showToast' => ['message' => 'Saved!']])
 *         ->pushUrl('/new-url')
 *         ->build();
 *
 * @link https://htmx.org/reference/#response_headers
 */
class HtmxResponseBuilder
{
    use HasDefaultViewDataTrait;

    private HtmxResponseHeaderCollection $headers;

    private ViewsCollection $views;

    private int $responseCode = 204;

    /**
     * @param bool $fromHtmxRequest Whether the request is from htmx or not.
     * @param array<string, mixed> $commonViewData View data that will be added to each view.
     * @param bool $setDefaultViewData Whether build-in view params should be added to each view.
     */
    private function __construct(
        bool $fromHtmxRequest,
        private readonly array $commonViewData = [],
        private readonly bool $setDefaultViewData = true,
    ) {
        $this->views = new ViewsCollection();
        $this->headers = new HtmxResponseHeaderCollection();
        $this->setDefaultViewData(Result::UNKNOWN, $fromHtmxRequest);
    }

    /**
     * Creates a new response builder instance.
     *
     * @param bool $fromHtmxRequest Whether the request originated from htmx (use $htmxRequest->isHtmx)
     * @param array<string, mixed> $viewData Common view data added to all views
     *
     * @example
     *     HtmxResponseBuilder::create($htmx->isHtmx)
     *         ->success()
     *         ->view('template.html.twig')
     *         ->build();
     */
    public static function create(bool $fromHtmxRequest, array $viewData = []): self
    {
        return new self($fromHtmxRequest, $viewData);
    }

    /**
     * Creates a builder with explicit configuration for default view data.
     * Used internally by HtmxResponseBuilderFactory to respect bundle configuration.
     *
     * @param array<string, mixed> $viewData
     *
     * @internal
     */
    public static function createWithConfig(bool $fromHtmxRequest, array $viewData, bool $setDefaultViewData): self
    {
        return new self($fromHtmxRequest, $viewData, $setDefaultViewData);
    }

    /**
     * Sets a custom HTTP response code with a result status.
     *
     * Use this for non-standard status codes. For common cases, prefer success() or failure().
     *
     * @param int $responseCode HTTP status code (e.g., 200, 201, 422)
     * @param Result $result The result status for view templates
     *
     * @example
     *     ->responseCode(201, Result::SUCCESS) // Created
     *     ->responseCode(409, Result::FAILURE) // Conflict
     */
    public function responseCode(int $responseCode, Result $result): self
    {
        $this->withResponseCode($responseCode);
        $this->defaultViewData[View::RESULT_VIEW_PARAM_NAME] = $result;

        return $this;
    }

    /**
     * Returns HTTP 204 No Content response.
     *
     * Clears all views and returns an empty response body.
     * Useful for actions that don't need to update the DOM (e.g., background operations).
     *
     * @param Result $result Optional result status (default: UNKNOWN)
     *
     * @example
     *     // Delete operation with no DOM update
     *     return HtmxResponseBuilder::create($htmx->isHtmx)
     *         ->noContent(Result::SUCCESS)
     *         ->trigger('itemDeleted')
     *         ->build();
     */
    public function noContent(Result $result = Result::UNKNOWN): self
    {
        $this->clearViews();
        $this->withResponseCode(204);
        $this->defaultViewData[View::RESULT_VIEW_PARAM_NAME] = $result;

        return $this;
    }

    /**
     * Sets HTTP 422 Unprocessable Entity status for validation errors.
     *
     * Use this when form validation fails. htmx will still swap the content
     * (unlike 4xx/5xx errors which are ignored by default).
     *
     * Note: Requires client-side handling to allow swap on 422:
     *     document.body.addEventListener('htmx:beforeOnLoad', function(evt) {
     *         if (evt.detail.xhr.status === 422) {
     *             evt.detail.shouldSwap = true;
     *             evt.detail.isError = false;
     *         }
     *     });
     *
     * @example
     *     if (!$form->isValid()) {
     *         return HtmxResponseBuilder::create($htmx->isHtmx)
     *             ->failure()
     *             ->view('form.html.twig', ['form' => $form])
     *             ->build();
     *     }
     */
    public function failure(): self
    {
        $this->withResponseCode(422);
        $this->defaultViewData[View::RESULT_VIEW_PARAM_NAME] = Result::FAILURE;

        return $this;
    }

    /**
     * Sets HTTP 200 OK status for successful operations.
     *
     * The most common response status for successful htmx requests.
     *
     * @example
     *     return HtmxResponseBuilder::create($htmx->isHtmx)
     *         ->success()
     *         ->view('success.html.twig', ['message' => 'Saved!'])
     *         ->build();
     */
    public function success(): self
    {
        $this->withResponseCode(200);
        $this->defaultViewData[View::RESULT_VIEW_PARAM_NAME] = Result::SUCCESS;

        return $this;
    }

    /**
     * Adds a custom htmx response header.
     *
     * For most cases, use the dedicated methods (trigger, redirect, pushUrl, etc.).
     * Use this for custom or advanced header configurations.
     *
     * @param HtmxResponseHeader $header The header to add
     *
     * @see trigger(), redirect(), pushUrl(), retarget(), withReswap()
     */
    public function header(HtmxResponseHeader $header): self
    {
        $this->headers = $this->headers->add($header);

        return $this;
    }

    /**
     * Adds a view (template) to render in the response.
     *
     * Can render a full template or a specific block within a template.
     * Multiple views can be added for Out of Band (OOB) swaps.
     *
     * @param string $template Twig template path (e.g., 'components/list.html.twig')
     * @param array<string, mixed> $viewData Data passed to the template
     * @param string|null $block Optional block name to render only that block
     *
     * @example Full template:
     *     ->view('page.html.twig', ['items' => $items])
     *
     * @example Specific block:
     *     ->view('page.html.twig', ['items' => $items], 'listBlock')
     *
     * @see viewBlock() For a more explicit block rendering syntax
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

    /**
     * Adds a specific block from a template to render.
     *
     * Shorthand for view($template, $viewData, $block).
     * Useful for partial updates where only a portion of the page needs to change.
     *
     * @param string $template Twig template path
     * @param string $block Block name defined in template ({% block blockName %})
     * @param array<string, mixed> $viewData Data passed to the template
     *
     * @example
     *     // In controller:
     *     ->viewBlock('products.html.twig', 'productList', ['products' => $products])
     *
     *     // In template (products.html.twig):
     *     {% block productList %}
     *         {% for product in products %}...{% endfor %}
     *     {% endblock %}
     */
    public function viewBlock(string $template, string $block, array $viewData = []): self
    {
        return $this->view($template, $viewData, $block);
    }

    /**
     * Clears all added views.
     *
     * Useful when switching from a view response to a no-content response.
     */
    public function clearViews(): self
    {
        $this->views = new ViewsCollection();

        return $this;
    }

    /**
     * Performs a client-side redirect without full page reload (HX-Location).
     *
     * Similar to redirect(), but uses htmx's location mechanism which:
     * - Does not trigger a full page reload
     * - Can include additional options like target and swap
     *
     * @param string $url The URL to navigate to
     *
     * @link https://htmx.org/headers/hx-location/
     *
     * @example
     *     ->location('/dashboard')
     */
    public function location(string $url): self
    {
        $this->header(new Location($url));

        return $this;
    }

    /**
     * Pushes a new URL into the browser's history stack (HX-Push-Url).
     *
     * Updates the browser's address bar and history without navigation.
     * User can use back/forward buttons to return to this state.
     *
     * @param string $url The URL to push to history
     *
     * @link https://htmx.org/headers/hx-push-url/
     *
     * @example
     *     // After loading a filtered list
     *     ->success()
     *     ->view('list.html.twig', ['items' => $filtered])
     *     ->pushUrl('/items?filter=active')
     */
    public function pushUrl(string $url): self
    {
        $this->header(new PushUrl($url));

        return $this;
    }

    /**
     * Performs a full page redirect (HX-Redirect).
     *
     * Triggers a traditional browser redirect with full page reload.
     * Use this for navigation that requires a complete page change.
     *
     * @param string $url The URL to redirect to
     *
     * @link https://htmx.org/headers/hx-redirect/
     *
     * @example
     *     // After successful login
     *     ->success()
     *     ->redirect('/dashboard')
     */
    public function redirect(string $url): self
    {
        $this->header(new Redirect($url));

        return $this;
    }

    /**
     * Triggers a full page refresh (HX-Refresh).
     *
     * Forces the browser to reload the current page.
     * Useful after operations that affect global state.
     *
     * @link https://htmx.org/headers/hx-refresh/
     *
     * @example
     *     // After changing user preferences that affect the whole page
     *     ->success()
     *     ->refresh()
     */
    public function refresh(): self
    {
        $this->header(new Refresh());

        return $this;
    }

    /**
     * Replaces the current URL in the browser's address bar (HX-Replace-Url).
     *
     * Unlike pushUrl(), this replaces the current history entry instead of adding a new one.
     * Back button will skip this URL.
     *
     * @param string $url The URL to set in the address bar
     *
     * @link https://htmx.org/headers/hx-replace-url/
     *
     * @example
     *     // After form submission, replace the URL without adding to history
     *     ->replaceUrl('/form/success')
     */
    public function replaceUrl(string $url): self
    {
        $this->header(new ReplaceUrl($url));

        return $this;
    }

    /**
     * Overrides how the response content will be swapped into the DOM (HX-Reswap).
     *
     * Allows server to change the swap behavior defined in the triggering element.
     *
     * @param SwapStyle $style How to swap: innerHTML, outerHTML, beforebegin, afterbegin, beforeend, afterend, delete, none
     * @param SwapModifier ...$modifiers Optional modifiers: transition, swap timing, settle timing, scroll, show, focus-scroll
     *
     * @link https://htmx.org/attributes/hx-swap/
     * @link https://htmx.org/headers/hx-reswap/
     *
     * @example
     *     use Mdxpl\HtmxBundle\Response\Swap\SwapStyle;
     *     use Mdxpl\HtmxBundle\Response\Swap\Modifiers\Transition;
     *
     *     ->withReswap(SwapStyle::OuterHTML, new Transition())
     */
    public function withReswap(SwapStyle $style, SwapModifier ...$modifiers): self
    {
        $this->header(new Reswap($style, ...$modifiers));

        return $this;
    }

    /**
     * Changes the target element for the content swap (HX-Retarget).
     *
     * Overrides the hx-target attribute on the triggering element.
     * Useful when the server needs to update a different element than originally specified.
     *
     * @param string $cssSelector CSS selector for the new target element (e.g., '#container', '.list')
     *
     * @link https://htmx.org/headers/hx-retarget/
     *
     * @example
     *     // Redirect update to a different container
     *     ->retarget('#notification-area')
     */
    public function retarget(string $cssSelector): self
    {
        $this->header(new Retarget($cssSelector));

        return $this;
    }

    /**
     * Selects which part of the response to swap (HX-Reselect).
     *
     * Overrides hx-select on the triggering element.
     * Allows server to specify which portion of the response HTML should be used.
     *
     * @param string $cssSelector CSS selector to extract from response (e.g., '#content', '.result')
     *
     * @link https://htmx.org/headers/hx-reselect/
     *
     * @example
     *     // Only swap the #results portion of the response
     *     ->reselect('#results')
     */
    public function reselect(string $cssSelector): self
    {
        $this->header(new Reselect($cssSelector));

        return $this;
    }

    /**
     * Triggers client-side events immediately when response is received (HX-Trigger).
     *
     * Events can be listened to with JavaScript for custom behavior like:
     * - Showing toast notifications
     * - Updating counters
     * - Triggering animations
     *
     * @param string|array<string, mixed> $events Event name(s) with optional data
     *
     * @link https://htmx.org/headers/hx-trigger/
     *
     * @example Simple event:
     *     ->trigger('itemDeleted')
     *
     * @example Event with data:
     *     ->trigger(['showToast' => ['message' => 'Item saved!', 'type' => 'success']])
     *
     * @example Multiple events:
     *     ->trigger(['itemUpdated' => null, 'refreshCounter' => ['count' => 5]])
     *
     * @example Client-side listener:
     *     document.body.addEventListener('showToast', function(evt) {
     *         showToast(evt.detail.message, evt.detail.type);
     *     });
     */
    public function trigger(string|array $events): self
    {
        $this->header(new Trigger($events));

        return $this;
    }

    /**
     * Triggers client-side events after the settling step (HX-Trigger-After-Settle).
     *
     * Fires after the DOM has settled (CSS transitions completed).
     * Useful for actions that depend on the final DOM state.
     *
     * @param string|array<string, mixed> $events Event name(s) with optional data
     *
     * @link https://htmx.org/headers/hx-trigger/
     *
     * @example
     *     ->triggerAfterSettle('initTooltips')
     *
     * @see trigger() For event syntax examples
     */
    public function triggerAfterSettle(string|array $events): self
    {
        $this->header(new TriggerAfterSettle($events));

        return $this;
    }

    /**
     * Triggers client-side events after the swap step (HX-Trigger-After-Swap).
     *
     * Fires after the content has been swapped but before settling.
     * Useful for immediate post-swap actions.
     *
     * @param string|array<string, mixed> $events Event name(s) with optional data
     *
     * @link https://htmx.org/headers/hx-trigger/
     *
     * @example
     *     ->triggerAfterSwap('contentSwapped')
     *
     * @see trigger() For event syntax examples
     */
    public function triggerAfterSwap(string|array $events): self
    {
        $this->header(new TriggerAfterSwap($events));

        return $this;
    }

    /**
     * Builds and returns the final HtmxResponse.
     *
     * Call this as the last method in the chain to get the response object
     * that can be returned from a controller.
     *
     * @return HtmxResponse The configured response ready to be sent
     *
     * @example
     *     return HtmxResponseBuilder::create($htmx->isHtmx)
     *         ->success()
     *         ->view('template.html.twig')
     *         ->build();
     */
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
