<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Htmx;

use Mdxpl\HtmxBundle\Form\Htmx\Trigger\Trigger;

/**
 * Fluent builder for htmx form field options.
 *
 * Usage:
 * ```php
 * $builder->add('search', TextType::class, [
 *     'htmx' => HtmxOptions::create()
 *         ->get('/search')
 *         ->trigger(Trigger::keyup()->changed()->delay(300))
 *         ->target('#results')
 *         ->indicator('#spinner'),
 * ]);
 * ```
 *
 * @link https://htmx.org/reference/#attributes
 */
final class HtmxOptions
{
    /** @var array<string, mixed> */
    private array $options = [];

    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    // ==========================================
    // HTTP Methods (URL)
    // ==========================================

    /**
     * Issues a GET request to the given URL.
     *
     * @link https://htmx.org/attributes/hx-get/
     */
    public function get(string $url): self
    {
        $this->options['get'] = $url;

        return $this;
    }

    /**
     * Issues a POST request to the given URL.
     *
     * @link https://htmx.org/attributes/hx-post/
     */
    public function post(string $url): self
    {
        $this->options['post'] = $url;

        return $this;
    }

    /**
     * Issues a PUT request to the given URL.
     *
     * @link https://htmx.org/attributes/hx-put/
     */
    public function put(string $url): self
    {
        $this->options['put'] = $url;

        return $this;
    }

    /**
     * Issues a PATCH request to the given URL.
     *
     * @link https://htmx.org/attributes/hx-patch/
     */
    public function patch(string $url): self
    {
        $this->options['patch'] = $url;

        return $this;
    }

    /**
     * Issues a DELETE request to the given URL.
     *
     * @link https://htmx.org/attributes/hx-delete/
     */
    public function delete(string $url): self
    {
        $this->options['delete'] = $url;

        return $this;
    }

    // ==========================================
    // HTTP Methods (Route)
    // ==========================================

    /**
     * Issues a GET request to the given Symfony route.
     *
     * @param string $route Route name
     * @param array<string, mixed> $params Route parameters
     *
     * @link https://htmx.org/attributes/hx-get/
     *
     * @example ->getRoute('app_search')
     * @example ->getRoute('app_search', ['query' => 'foo'])
     */
    public function getRoute(string $route, array $params = []): self
    {
        $this->options['get'] = new Route($route, $params);

        return $this;
    }

    /**
     * Issues a POST request to the given Symfony route.
     *
     * @param string $route Route name
     * @param array<string, mixed> $params Route parameters
     *
     * @link https://htmx.org/attributes/hx-post/
     */
    public function postRoute(string $route, array $params = []): self
    {
        $this->options['post'] = new Route($route, $params);

        return $this;
    }

    /**
     * Issues a PUT request to the given Symfony route.
     *
     * @param string $route Route name
     * @param array<string, mixed> $params Route parameters
     *
     * @link https://htmx.org/attributes/hx-put/
     */
    public function putRoute(string $route, array $params = []): self
    {
        $this->options['put'] = new Route($route, $params);

        return $this;
    }

    /**
     * Issues a PATCH request to the given Symfony route.
     *
     * @param string $route Route name
     * @param array<string, mixed> $params Route parameters
     *
     * @link https://htmx.org/attributes/hx-patch/
     */
    public function patchRoute(string $route, array $params = []): self
    {
        $this->options['patch'] = new Route($route, $params);

        return $this;
    }

    /**
     * Issues a DELETE request to the given Symfony route.
     *
     * @param string $route Route name
     * @param array<string, mixed> $params Route parameters
     *
     * @link https://htmx.org/attributes/hx-delete/
     */
    public function deleteRoute(string $route, array $params = []): self
    {
        $this->options['delete'] = new Route($route, $params);

        return $this;
    }

    // ==========================================
    // Core Attributes
    // ==========================================

    /**
     * Specifies the event that triggers the request.
     *
     * @param string|Trigger $trigger Event specification
     *
     * @link https://htmx.org/attributes/hx-trigger/
     *
     * @example
     * ->trigger('click')
     * ->trigger('keyup changed delay:300ms')
     * ->trigger(Trigger::keyup()->changed()->delay(300))
     */
    public function trigger(string|Trigger $trigger): self
    {
        $this->options['trigger'] = (string) $trigger;

        return $this;
    }

    /**
     * Specifies the target element for the response.
     *
     * @param string $selector CSS selector for target element
     *
     * @link https://htmx.org/attributes/hx-target/
     *
     * @example
     * ->target('#results')
     * ->target('closest tr')
     * ->target('find .content')
     */
    public function target(string $selector): self
    {
        $this->options['target'] = $selector;

        return $this;
    }

    /**
     * Specifies how the response will be swapped into the target.
     *
     * @param string|SwapStyle $swap Swap style
     *
     * @link https://htmx.org/attributes/hx-swap/
     *
     * @example
     * ->swap('innerHTML')
     * ->swap(SwapStyle::OuterHTML)
     * ->swap('innerHTML swap:300ms')
     */
    public function swap(string|SwapStyle $swap): self
    {
        $this->options['swap'] = (string) ($swap instanceof SwapStyle ? $swap->value : $swap);

        return $this;
    }

    /**
     * Specifies an element to show during the request.
     *
     * @param string $selector CSS selector for indicator element
     *
     * @link https://htmx.org/attributes/hx-indicator/
     */
    public function indicator(string $selector): self
    {
        $this->options['indicator'] = $selector;

        return $this;
    }

    // ==========================================
    // Request Modifiers
    // ==========================================

    /**
     * Specifies additional elements to include in the request.
     *
     * @param string $selector CSS selector for elements to include
     *
     * @link https://htmx.org/attributes/hx-include/
     */
    public function include(string $selector): self
    {
        $this->options['include'] = $selector;

        return $this;
    }

    /**
     * Adds values to the request parameters.
     *
     * @param array<string, mixed>|string $vals Values as array or JSON string
     *
     * @link https://htmx.org/attributes/hx-vals/
     */
    public function vals(array|string $vals): self
    {
        $this->options['vals'] = $vals;

        return $this;
    }

    /**
     * Filters the parameters that will be submitted.
     *
     * @param string $params Parameter specification ('*', 'none', or comma-separated list)
     *
     * @link https://htmx.org/attributes/hx-params/
     */
    public function params(string $params): self
    {
        $this->options['params'] = $params;

        return $this;
    }

    /**
     * Adds custom headers to the request.
     *
     * @param array<string, string>|string $headers Headers as array or JSON string
     *
     * @link https://htmx.org/attributes/hx-headers/
     */
    public function headers(array|string $headers): self
    {
        $this->options['headers'] = $headers;

        return $this;
    }

    // ==========================================
    // Response Handling
    // ==========================================

    /**
     * Selects a subset of the response to swap in.
     *
     * @param string $selector CSS selector for content to select
     *
     * @link https://htmx.org/attributes/hx-select/
     */
    public function select(string $selector): self
    {
        $this->options['select'] = $selector;

        return $this;
    }

    /**
     * Selects content for out-of-band swap.
     *
     * @param string $selector CSS selector for OOB content
     *
     * @link https://htmx.org/attributes/hx-select-oob/
     */
    public function selectOob(string $selector): self
    {
        $this->options['select-oob'] = $selector;

        return $this;
    }

    // ==========================================
    // User Interaction
    // ==========================================

    /**
     * Shows a confirmation dialog before the request.
     *
     * @param string $message Confirmation message
     *
     * @link https://htmx.org/attributes/hx-confirm/
     */
    public function confirm(string $message): self
    {
        $this->options['confirm'] = $message;

        return $this;
    }

    /**
     * Shows a prompt dialog before the request.
     *
     * @param string $message Prompt message
     *
     * @link https://htmx.org/attributes/hx-prompt/
     */
    public function prompt(string $message): self
    {
        $this->options['prompt'] = $message;

        return $this;
    }

    // ==========================================
    // URL/History
    // ==========================================

    /**
     * Pushes the URL into the browser history.
     *
     * @param bool|string $url True to use response URL, or custom URL
     *
     * @link https://htmx.org/attributes/hx-push-url/
     */
    public function pushUrl(bool|string $url = true): self
    {
        $this->options['push-url'] = $url;

        return $this;
    }

    /**
     * Replaces the current URL in browser history.
     *
     * @param bool|string $url True to use response URL, or custom URL
     *
     * @link https://htmx.org/attributes/hx-replace-url/
     */
    public function replaceUrl(bool|string $url = true): self
    {
        $this->options['replace-url'] = $url;

        return $this;
    }

    // ==========================================
    // Synchronization
    // ==========================================

    /**
     * Synchronizes requests with another element.
     *
     * @param string $sync Sync specification (e.g., 'closest form:abort')
     *
     * @link https://htmx.org/attributes/hx-sync/
     */
    public function sync(string $sync): self
    {
        $this->options['sync'] = $sync;

        return $this;
    }

    /**
     * Disables elements during the request.
     *
     * @param string $selector CSS selector for elements to disable
     *
     * @link https://htmx.org/attributes/hx-disabled-elt/
     */
    public function disabledElt(string $selector): self
    {
        $this->options['disabled-elt'] = $selector;

        return $this;
    }

    // ==========================================
    // Event Handlers
    // ==========================================

    /**
     * Adds an htmx event handler.
     *
     * @param string $event Event name (without 'htmx:' prefix)
     * @param string $script JavaScript to execute
     *
     * @link https://htmx.org/attributes/hx-on/
     *
     * @example
     * ->on('before-request', 'console.log("requesting...")')
     * ->on('config-request', 'event.detail.path = event.detail.path.replace("__VALUE__", this.value)')
     */
    public function on(string $event, string $script): self
    {
        $this->options['on::' . $event] = $script;

        return $this;
    }

    /**
     * Handler for htmx:before-request event.
     */
    public function onBeforeRequest(string $script): self
    {
        return $this->on('before-request', $script);
    }

    /**
     * Handler for htmx:after-request event.
     */
    public function onAfterRequest(string $script): self
    {
        return $this->on('after-request', $script);
    }

    /**
     * Handler for htmx:config-request event.
     * Useful for modifying the request before it is sent.
     */
    public function onConfigRequest(string $script): self
    {
        return $this->on('config-request', $script);
    }

    /**
     * Handler for htmx:before-swap event.
     */
    public function onBeforeSwap(string $script): self
    {
        return $this->on('before-swap', $script);
    }

    /**
     * Handler for htmx:after-swap event.
     */
    public function onAfterSwap(string $script): self
    {
        return $this->on('after-swap', $script);
    }

    /**
     * Handler for htmx:after-settle event.
     */
    public function onAfterSettle(string $script): self
    {
        return $this->on('after-settle', $script);
    }

    // ==========================================
    // Miscellaneous
    // ==========================================

    /**
     * Enables htmx boost on the element.
     *
     * @link https://htmx.org/attributes/hx-boost/
     */
    public function boost(bool $enabled = true): self
    {
        $this->options['boost'] = $enabled;

        return $this;
    }

    /**
     * Enables htmx extensions.
     *
     * @param string $extensions Comma-separated list of extensions
     *
     * @link https://htmx.org/attributes/hx-ext/
     */
    public function ext(string $extensions): self
    {
        $this->options['ext'] = $extensions;

        return $this;
    }

    /**
     * Sets a raw htmx option.
     *
     * Use this for options not covered by the builder methods.
     *
     * @param string $key Option key (without 'hx-' prefix)
     * @param mixed $value Option value
     */
    public function set(string $key, mixed $value): self
    {
        $this->options[$key] = $value;

        return $this;
    }

    // ==========================================
    // Output
    // ==========================================

    /**
     * Returns the options array for use with HtmxTypeExtension.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->options;
    }
}
