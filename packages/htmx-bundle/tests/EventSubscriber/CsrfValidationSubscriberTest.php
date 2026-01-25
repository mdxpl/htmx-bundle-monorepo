<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\EventSubscriber;

use Mdxpl\HtmxBundle\EventSubscriber\CsrfValidationSubscriber;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfValidationSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        self::assertSame(
            [KernelEvents::REQUEST => ['onKernelRequest', 8]],
            CsrfValidationSubscriber::getSubscribedEvents(),
        );
    }

    public function testDoesNothingWhenDisabled(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->expects($this->never())->method('isTokenValid');

        $subscriber = new CsrfValidationSubscriber($tokenManager, enabled: false);

        $request = $this->createHtmxRequest('POST');
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);
    }

    public function testDoesNothingForNonMainRequest(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->expects($this->never())->method('isTokenValid');

        $subscriber = new CsrfValidationSubscriber($tokenManager);

        $request = $this->createHtmxRequest('POST');
        $event = $this->createRequestEvent($request, isMainRequest: false);

        $subscriber->onKernelRequest($event);
    }

    public function testDoesNothingForNonHtmxRequest(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->expects($this->never())->method('isTokenValid');

        $subscriber = new CsrfValidationSubscriber($tokenManager);

        $request = new Request();
        $request->setMethod('POST');
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);
    }

    public function testDoesNothingForSafeMethods(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->expects($this->never())->method('isTokenValid');

        $subscriber = new CsrfValidationSubscriber($tokenManager);

        foreach (['GET', 'HEAD', 'OPTIONS'] as $method) {
            $request = $this->createHtmxRequest($method);
            $event = $this->createRequestEvent($request);

            $subscriber->onKernelRequest($event);
        }
    }

    public function testThrowsExceptionWhenTokenMissing(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $subscriber = new CsrfValidationSubscriber($tokenManager);

        $request = $this->createHtmxRequest('POST');
        $event = $this->createRequestEvent($request);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('CSRF token missing.');

        $subscriber->onKernelRequest($event);
    }

    public function testThrowsExceptionWhenTokenInvalid(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->expects($this->once())
            ->method('isTokenValid')
            ->with($this->callback(fn (CsrfToken $token) => $token->getValue() === 'invalid-token' && $token->getId() === 'mdx-htmx'))
            ->willReturn(false);

        $subscriber = new CsrfValidationSubscriber($tokenManager);

        $request = $this->createHtmxRequest('POST');
        $request->headers->set('X-CSRF-Token', 'invalid-token');
        $event = $this->createRequestEvent($request);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('Invalid CSRF token.');

        $subscriber->onKernelRequest($event);
    }

    public function testPassesWithValidToken(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->expects($this->once())
            ->method('isTokenValid')
            ->with($this->callback(fn (CsrfToken $token) => $token->getValue() === 'valid-token' && $token->getId() === 'mdx-htmx'))
            ->willReturn(true);

        $subscriber = new CsrfValidationSubscriber($tokenManager);

        $request = $this->createHtmxRequest('POST');
        $request->headers->set('X-CSRF-Token', 'valid-token');
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);

        self::assertTrue(true);
    }

    public function testUsesCustomTokenId(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->expects($this->once())
            ->method('isTokenValid')
            ->with($this->callback(fn (CsrfToken $token) => $token->getId() === 'custom-token-id'))
            ->willReturn(true);

        $subscriber = new CsrfValidationSubscriber($tokenManager, tokenId: 'custom-token-id');

        $request = $this->createHtmxRequest('POST');
        $request->headers->set('X-CSRF-Token', 'token-value');
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);
    }

    public function testUsesCustomHeaderName(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->expects($this->once())
            ->method('isTokenValid')
            ->willReturn(true);

        $subscriber = new CsrfValidationSubscriber($tokenManager, headerName: 'X-Custom-Header');

        $request = $this->createHtmxRequest('POST');
        $request->headers->set('X-Custom-Header', 'token-value');
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);
    }

    public function testValidatesForAllNonSafeMethods(): void
    {
        foreach (['POST', 'PUT', 'DELETE', 'PATCH'] as $method) {
            $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
            $tokenManager->expects($this->once())
                ->method('isTokenValid')
                ->willReturn(true);

            $subscriber = new CsrfValidationSubscriber($tokenManager);

            $request = $this->createHtmxRequest($method);
            $request->headers->set('X-CSRF-Token', 'valid-token');
            $event = $this->createRequestEvent($request);

            $subscriber->onKernelRequest($event);
        }
    }

    public function testCustomSafeMethods(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->expects($this->never())->method('isTokenValid');

        $subscriber = new CsrfValidationSubscriber($tokenManager, safeMethods: ['GET', 'POST']);

        $request = $this->createHtmxRequest('POST');
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);
    }

    public function testDoesNothingWhenHtmxRequestAttributeIsNotHtmx(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->expects($this->never())->method('isTokenValid');

        $subscriber = new CsrfValidationSubscriber($tokenManager);

        $request = new Request();
        $request->setMethod('POST');
        $request->attributes->set(HtmxRequest::REQUEST_ATTRIBUTE_NAME, new HtmxRequest(isHtmx: false));
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);
    }

    private function createHtmxRequest(string $method): Request
    {
        $request = new Request();
        $request->setMethod($method);
        $request->attributes->set(HtmxRequest::REQUEST_ATTRIBUTE_NAME, new HtmxRequest(isHtmx: true));

        return $request;
    }

    private function createRequestEvent(Request $request, bool $isMainRequest = true): RequestEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new RequestEvent(
            $kernel,
            $request,
            $isMainRequest ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::SUB_REQUEST,
        );
    }
}
