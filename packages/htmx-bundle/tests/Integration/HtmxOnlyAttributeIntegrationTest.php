<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Integration;

use Mdxpl\HtmxBundle\Attribute\HtmxOnly;
use Mdxpl\HtmxBundle\EventSubscriber\HtmxOnlyAttributeSubscriber;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Integration tests for #[HtmxOnly] attribute.
 *
 * The HtmxOnly attribute restricts controller actions to htmx requests only.
 */
class HtmxOnlyAttributeIntegrationTest extends TestCase
{
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

    private function createControllerEvent(Request $request, bool $hasAttribute): ControllerEvent
    {
        $controller = new TestController();
        $method = $hasAttribute ? 'htmxOnlyAction' : 'regularAction';

        return new ControllerEvent(
            $this->createMock(KernelInterface::class),
            [$controller, $method],
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );
    }

    /**
     * Tests that htmx request passes through when #[HtmxOnly] is present.
     */
    public function testHtmxRequestWithAttributePasses(): void
    {
        $subscriber = new HtmxOnlyAttributeSubscriber();
        $request = $this->createRequest(isHtmx: true);
        $event = $this->createControllerEvent($request, hasAttribute: true);

        $subscriber->onKernelController($event);

        $this->assertTrue(true);
    }

    /**
     * Tests that non-htmx request is blocked when #[HtmxOnly] is present.
     */
    public function testNonHtmxRequestWithAttributeThrowsNotFound(): void
    {
        $subscriber = new HtmxOnlyAttributeSubscriber();
        $request = $this->createRequest(isHtmx: false);
        $event = $this->createControllerEvent($request, hasAttribute: true);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Not Found');

        $subscriber->onKernelController($event);
    }

    /**
     * Tests that non-htmx request passes when #[HtmxOnly] is NOT present.
     */
    public function testNonHtmxRequestWithoutAttributePasses(): void
    {
        $subscriber = new HtmxOnlyAttributeSubscriber();
        $request = $this->createRequest(isHtmx: false);
        $event = $this->createControllerEvent($request, hasAttribute: false);

        $subscriber->onKernelController($event);

        $this->assertTrue(true);
    }

    /**
     * Tests that htmx request passes when #[HtmxOnly] is NOT present.
     */
    public function testHtmxRequestWithoutAttributePasses(): void
    {
        $subscriber = new HtmxOnlyAttributeSubscriber();
        $request = $this->createRequest(isHtmx: true);
        $event = $this->createControllerEvent($request, hasAttribute: false);

        $subscriber->onKernelController($event);

        $this->assertTrue(true);
    }

    /**
     * Tests default 404 status code when blocking non-htmx request.
     */
    public function testDefaultStatusCodeIs404(): void
    {
        $subscriber = new HtmxOnlyAttributeSubscriber();
        $request = $this->createRequest(isHtmx: false);
        $event = $this->createControllerEvent($request, hasAttribute: true);

        try {
            $subscriber->onKernelController($event);
            $this->fail('Expected HttpException');
        } catch (HttpException $e) {
            $this->assertSame(Response::HTTP_NOT_FOUND, $e->getStatusCode());
        }
    }

    /**
     * Tests custom 403 Forbidden status code configuration.
     */
    public function testCustomStatusCode403(): void
    {
        $subscriber = new HtmxOnlyAttributeSubscriber(
            statusCode: Response::HTTP_FORBIDDEN,
            message: 'Access Denied',
        );
        $request = $this->createRequest(isHtmx: false);
        $event = $this->createControllerEvent($request, hasAttribute: true);

        try {
            $subscriber->onKernelController($event);
            $this->fail('Expected HttpException');
        } catch (HttpException $e) {
            $this->assertSame(Response::HTTP_FORBIDDEN, $e->getStatusCode());
            $this->assertSame('Access Denied', $e->getMessage());
        }
    }

    /**
     * Tests custom 400 Bad Request status code configuration.
     */
    public function testCustomStatusCode400(): void
    {
        $subscriber = new HtmxOnlyAttributeSubscriber(
            statusCode: Response::HTTP_BAD_REQUEST,
            message: 'Bad Request - HTMX required',
        );
        $request = $this->createRequest(isHtmx: false);
        $event = $this->createControllerEvent($request, hasAttribute: true);

        try {
            $subscriber->onKernelController($event);
            $this->fail('Expected HttpException');
        } catch (HttpException $e) {
            $this->assertSame(Response::HTTP_BAD_REQUEST, $e->getStatusCode());
            $this->assertSame('Bad Request - HTMX required', $e->getMessage());
        }
    }

    /**
     * Tests that subscriber can be disabled via configuration.
     */
    public function testDisabledSubscriberAllowsNonHtmxRequest(): void
    {
        $subscriber = new HtmxOnlyAttributeSubscriber(enabled: false);
        $request = $this->createRequest(isHtmx: false);
        $event = $this->createControllerEvent($request, hasAttribute: true);

        $subscriber->onKernelController($event);

        $this->assertTrue(true);
    }

    /**
     * Tests request without HtmxRequest attribute set (edge case).
     */
    public function testRequestWithoutHtmxRequestAttribute(): void
    {
        $subscriber = new HtmxOnlyAttributeSubscriber();
        $request = Request::create('/test', 'GET');

        $controller = new TestController();

        $event = new ControllerEvent(
            $this->createMock(KernelInterface::class),
            [$controller, 'htmxOnlyAction'],
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );

        $this->expectException(HttpException::class);

        $subscriber->onKernelController($event);
    }
}

/**
 * Test controller with HtmxOnly attribute on method.
 */
class TestController
{
    #[HtmxOnly]
    public function htmxOnlyAction(): Response
    {
        return new Response('HTMX Only');
    }

    public function regularAction(): Response
    {
        return new Response('Regular');
    }
}
