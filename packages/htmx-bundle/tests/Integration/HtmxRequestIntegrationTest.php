<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Integration;

use Mdxpl\HtmxBundle\EventSubscriber\HtmxRequestSubscriber;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Integration tests for HtmxRequest parsing from HTTP headers.
 *
 * Tests that all htmx request headers are correctly parsed and available.
 *
 * @see https://htmx.org/reference/#request_headers
 */
class HtmxRequestIntegrationTest extends TestCase
{
    private function processRequest(Request $request): HtmxRequest
    {
        $subscriber = new HtmxRequestSubscriber();

        $event = new RequestEvent(
            $this->createMock(KernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );

        $subscriber->onKernelRequest($event);

        return $request->attributes->get(HtmxRequest::REQUEST_ATTRIBUTE_NAME);
    }

    /**
     * Tests basic htmx request detection via HX-Request header.
     */
    public function testIsHtmxDetection(): void
    {
        $request = Request::create('/test', 'GET');
        $request->headers->set('HX-Request', 'true');

        $htmxRequest = $this->processRequest($request);

        self::assertTrue($htmxRequest->isHtmx);
    }

    /**
     * Tests non-htmx request detection.
     */
    public function testNonHtmxRequest(): void
    {
        $request = Request::create('/test', 'GET');

        $htmxRequest = $this->processRequest($request);

        self::assertFalse($htmxRequest->isHtmx);
    }

    /**
     * Tests HX-Boosted header parsing.
     * Indicates request is via an element using hx-boost.
     */
    public function testIsBoostedHeader(): void
    {
        $request = Request::create('/test', 'GET');
        $request->headers->set('HX-Request', 'true');
        $request->headers->set('HX-Boosted', 'true');

        $htmxRequest = $this->processRequest($request);

        self::assertTrue($htmxRequest->isHtmx);
        self::assertTrue($htmxRequest->isBoosted);
    }

    /**
     * Tests HX-History-Restore-Request header parsing.
     * Indicates request is for history restoration after local cache miss.
     */
    public function testIsForHistoryRestorationHeader(): void
    {
        $request = Request::create('/test', 'GET');
        $request->headers->set('HX-Request', 'true');
        $request->headers->set('HX-History-Restore-Request', 'true');

        $htmxRequest = $this->processRequest($request);

        self::assertTrue($htmxRequest->isHtmx);
        self::assertTrue($htmxRequest->isForHistoryRestoration);
    }

    /**
     * Tests HX-Current-URL header parsing.
     * Contains the current URL of the browser.
     */
    public function testCurrentUrlHeader(): void
    {
        $request = Request::create('/test', 'GET');
        $request->headers->set('HX-Request', 'true');
        $request->headers->set('HX-Current-URL', 'https://example.com/page?foo=bar');

        $htmxRequest = $this->processRequest($request);

        self::assertTrue($htmxRequest->isHtmx);
        self::assertSame('https://example.com/page?foo=bar', $htmxRequest->currentUrl);
    }

    /**
     * Tests HX-Prompt header parsing.
     * Contains the user response to an hx-prompt.
     */
    public function testPromptHeader(): void
    {
        $request = Request::create('/test', 'DELETE');
        $request->headers->set('HX-Request', 'true');
        $request->headers->set('HX-Prompt', 'User confirmed deletion');

        $htmxRequest = $this->processRequest($request);

        self::assertTrue($htmxRequest->isHtmx);
        self::assertSame('User confirmed deletion', $htmxRequest->prompt);
    }

    /**
     * Tests HX-Target header parsing.
     * Contains the id of the target element if it exists.
     */
    public function testTargetHeader(): void
    {
        $request = Request::create('/test', 'GET');
        $request->headers->set('HX-Request', 'true');
        $request->headers->set('HX-Target', 'main-content');

        $htmxRequest = $this->processRequest($request);

        self::assertTrue($htmxRequest->isHtmx);
        self::assertSame('main-content', $htmxRequest->target);
    }

    /**
     * Tests HX-Trigger-Name header parsing.
     * Contains the name of the triggered element if it exists.
     */
    public function testTriggerNameHeader(): void
    {
        $request = Request::create('/test', 'POST');
        $request->headers->set('HX-Request', 'true');
        $request->headers->set('HX-Trigger-Name', 'submit-button');

        $htmxRequest = $this->processRequest($request);

        self::assertTrue($htmxRequest->isHtmx);
        self::assertSame('submit-button', $htmxRequest->triggerName);
    }

    /**
     * Tests HX-Trigger header parsing.
     * Contains the id of the triggered element if it exists.
     */
    public function testTriggerHeader(): void
    {
        $request = Request::create('/test', 'POST');
        $request->headers->set('HX-Request', 'true');
        $request->headers->set('HX-Trigger', 'btn-save');

        $htmxRequest = $this->processRequest($request);

        self::assertTrue($htmxRequest->isHtmx);
        self::assertSame('btn-save', $htmxRequest->trigger);
    }

    /**
     * Tests all headers combined in a single request.
     */
    public function testAllHeadersCombined(): void
    {
        $request = Request::create('/test', 'POST');
        $request->headers->set('HX-Request', 'true');
        $request->headers->set('HX-Boosted', 'true');
        $request->headers->set('HX-History-Restore-Request', 'true');
        $request->headers->set('HX-Current-URL', 'https://example.com/current');
        $request->headers->set('HX-Prompt', 'User input');
        $request->headers->set('HX-Target', 'content-area');
        $request->headers->set('HX-Trigger-Name', 'action-btn');
        $request->headers->set('HX-Trigger', 'trigger-id');

        $htmxRequest = $this->processRequest($request);

        self::assertTrue($htmxRequest->isHtmx);
        self::assertTrue($htmxRequest->isBoosted);
        self::assertTrue($htmxRequest->isForHistoryRestoration);
        self::assertSame('https://example.com/current', $htmxRequest->currentUrl);
        self::assertSame('User input', $htmxRequest->prompt);
        self::assertSame('content-area', $htmxRequest->target);
        self::assertSame('action-btn', $htmxRequest->triggerName);
        self::assertSame('trigger-id', $htmxRequest->trigger);
    }

    /**
     * Tests that missing optional headers return null.
     */
    public function testMissingOptionalHeadersAreNull(): void
    {
        $request = Request::create('/test', 'GET');
        $request->headers->set('HX-Request', 'true');

        $htmxRequest = $this->processRequest($request);

        self::assertTrue($htmxRequest->isHtmx);
        self::assertFalse($htmxRequest->isBoosted);
        self::assertFalse($htmxRequest->isForHistoryRestoration);
        self::assertNull($htmxRequest->currentUrl);
        self::assertNull($htmxRequest->prompt);
        self::assertNull($htmxRequest->target);
        self::assertNull($htmxRequest->triggerName);
        self::assertNull($htmxRequest->trigger);
    }

    /**
     * Tests HtmxRequest with special characters in headers.
     */
    public function testSpecialCharactersInHeaders(): void
    {
        $request = Request::create('/test', 'GET');
        $request->headers->set('HX-Request', 'true');
        $request->headers->set('HX-Current-URL', 'https://example.com/search?q=test&page=1#section');
        $request->headers->set('HX-Prompt', 'User said: "Yes, delete it!"');
        $request->headers->set('HX-Target', 'my-element-123');

        $htmxRequest = $this->processRequest($request);

        self::assertSame('https://example.com/search?q=test&page=1#section', $htmxRequest->currentUrl);
        self::assertSame('User said: "Yes, delete it!"', $htmxRequest->prompt);
        self::assertSame('my-element-123', $htmxRequest->target);
    }

    /**
     * Tests that sub-requests are skipped.
     * Sub-requests (ESI, render(), forward) should not process htmx headers
     * as they are internal Symfony requests, not browser requests.
     */
    public function testSubRequestIsSkipped(): void
    {
        $request = Request::create('/test', 'GET');
        $request->headers->set('HX-Request', 'true');

        $subscriber = new HtmxRequestSubscriber();

        $event = new RequestEvent(
            $this->createMock(KernelInterface::class),
            $request,
            HttpKernelInterface::SUB_REQUEST,
        );

        $subscriber->onKernelRequest($event);

        self::assertNull($request->attributes->get(HtmxRequest::REQUEST_ATTRIBUTE_NAME));
    }

    /**
     * Tests typical delete button scenario with prompt.
     */
    public function testDeleteButtonWithPromptScenario(): void
    {
        $request = Request::create('/items/123', 'DELETE');
        $request->headers->set('HX-Request', 'true');
        $request->headers->set('HX-Target', 'item-row-123');
        $request->headers->set('HX-Trigger', 'delete-btn-123');
        $request->headers->set('HX-Prompt', 'DELETE');
        $request->headers->set('HX-Current-URL', 'https://example.com/items');

        $htmxRequest = $this->processRequest($request);

        self::assertTrue($htmxRequest->isHtmx);
        self::assertSame('item-row-123', $htmxRequest->target);
        self::assertSame('delete-btn-123', $htmxRequest->trigger);
        self::assertSame('DELETE', $htmxRequest->prompt);
        self::assertSame('https://example.com/items', $htmxRequest->currentUrl);
    }

    /**
     * Tests boosted link navigation scenario.
     */
    public function testBoostedLinkNavigationScenario(): void
    {
        $request = Request::create('/about', 'GET');
        $request->headers->set('HX-Request', 'true');
        $request->headers->set('HX-Boosted', 'true');
        $request->headers->set('HX-Target', 'body');
        $request->headers->set('HX-Current-URL', 'https://example.com/home');

        $htmxRequest = $this->processRequest($request);

        self::assertTrue($htmxRequest->isHtmx);
        self::assertTrue($htmxRequest->isBoosted);
        self::assertSame('body', $htmxRequest->target);
        self::assertSame('https://example.com/home', $htmxRequest->currentUrl);
    }
}
