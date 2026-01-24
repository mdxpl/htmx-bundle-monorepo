<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Integration;

use LogicException;
use Mdxpl\HtmxBundle\EventSubscriber\HtmxResponseSubscriber;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Integration tests for strict mode.
 *
 * Strict mode throws an exception when HtmxResponse is returned for non-htmx requests.
 * This helps catch bugs where htmx-only responses are accidentally returned for regular requests.
 */
class StrictModeIntegrationTest extends TestCase
{
    use TwigIntegrationTestTrait;

    protected function setUp(): void
    {
        $this->setUpTwig();
    }

    private function createRequest(bool $isHtmx): Request
    {
        $request = Request::create('/test', 'GET');

        if ($isHtmx) {
            $request->headers->set('HX-Request', 'true');
        }

        $request->attributes->set(
            HtmxRequest::REQUEST_ATTRIBUTE_NAME,
            new HtmxRequest(isHtmx: $isHtmx),
        );

        return $request;
    }

    private function createViewEvent(Request $request, HtmxResponse $htmxResponse): ViewEvent
    {
        return new ViewEvent(
            $this->createMock(KernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $htmxResponse,
        );
    }

    /**
     * Tests that strict mode allows htmx response for htmx request.
     */
    public function testStrictModeAllowsHtmxResponseForHtmxRequest(): void
    {
        $subscriber = new HtmxResponseSubscriber(
            $this->responseFactory,
            strictMode: true,
        );

        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->viewBlock('withDefaultBlocks.html.twig', 'success')
            ->build();

        $request = $this->createRequest(isHtmx: true);
        $event = $this->createViewEvent($request, $htmxResponse);

        $subscriber->onKernelView($event);

        $this->assertInstanceOf(Response::class, $event->getResponse());
        $this->assertSame(200, $event->getResponse()->getStatusCode());
    }

    /**
     * Tests that strict mode throws exception for htmx response on non-htmx request.
     */
    public function testStrictModeThrowsForHtmxResponseOnNonHtmxRequest(): void
    {
        $subscriber = new HtmxResponseSubscriber(
            $this->responseFactory,
            strictMode: true,
        );

        $htmxResponse = HtmxResponseBuilder::create(false)
            ->success()
            ->viewBlock('withDefaultBlocks.html.twig', 'success')
            ->build();

        $request = $this->createRequest(isHtmx: false);
        $event = $this->createViewEvent($request, $htmxResponse);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('HtmxResponse returned for non-htmx request');

        $subscriber->onKernelView($event);
    }

    /**
     * Tests that strict mode disabled allows htmx response for non-htmx request.
     */
    public function testStrictModeDisabledAllowsHtmxResponseForNonHtmxRequest(): void
    {
        $subscriber = new HtmxResponseSubscriber(
            $this->responseFactory,
            strictMode: false,
        );

        $htmxResponse = HtmxResponseBuilder::create(false)
            ->success()
            ->viewBlock('withDefaultBlocks.html.twig', 'success')
            ->build();

        $request = $this->createRequest(isHtmx: false);
        $event = $this->createViewEvent($request, $htmxResponse);

        $subscriber->onKernelView($event);

        $this->assertInstanceOf(Response::class, $event->getResponse());
    }

    /**
     * Tests strict mode with no content response.
     */
    public function testStrictModeWithNoContentResponse(): void
    {
        $subscriber = new HtmxResponseSubscriber(
            $this->responseFactory,
            strictMode: true,
        );

        $htmxResponse = HtmxResponseBuilder::create(true)
            ->noContent()
            ->trigger('itemDeleted')
            ->build();

        $request = $this->createRequest(isHtmx: true);
        $event = $this->createViewEvent($request, $htmxResponse);

        $subscriber->onKernelView($event);

        $this->assertSame(204, $event->getResponse()->getStatusCode());
        $this->assertSame('itemDeleted', $event->getResponse()->headers->get('HX-Trigger'));
    }

    /**
     * Tests strict mode throws for no content response on non-htmx request.
     */
    public function testStrictModeThrowsForNoContentOnNonHtmxRequest(): void
    {
        $subscriber = new HtmxResponseSubscriber(
            $this->responseFactory,
            strictMode: true,
        );

        $htmxResponse = HtmxResponseBuilder::create(false)
            ->noContent()
            ->build();

        $request = $this->createRequest(isHtmx: false);
        $event = $this->createViewEvent($request, $htmxResponse);

        $this->expectException(LogicException::class);

        $subscriber->onKernelView($event);
    }

    /**
     * Tests request without HtmxRequest attribute does not throw.
     * When HtmxRequest attribute is missing, strict mode check is skipped
     * (the condition is `$htmxRequest && !$htmxRequest->isHtmx`).
     */
    public function testStrictModeWithMissingHtmxRequestAttributeDoesNotThrow(): void
    {
        $subscriber = new HtmxResponseSubscriber(
            $this->responseFactory,
            strictMode: true,
        );

        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->viewBlock('withDefaultBlocks.html.twig', 'success')
            ->build();

        $request = Request::create('/test', 'GET');

        $event = $this->createViewEvent($request, $htmxResponse);

        $subscriber->onKernelView($event);

        $this->assertInstanceOf(Response::class, $event->getResponse());
    }
}
