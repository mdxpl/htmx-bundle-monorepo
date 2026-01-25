<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Integration;

use Mdxpl\HtmxBundle\EventSubscriber\CsrfValidationSubscriber;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Integration tests for CSRF validation.
 *
 * @see docs/examples/csrf.md
 */
class CsrfValidationIntegrationTest extends TestCase
{
    private const TOKEN_ID = 'mdx-htmx';
    private const HEADER_NAME = 'X-CSRF-Token';
    private const VALID_TOKEN = 'valid-csrf-token-123';

    private function createTokenManager(bool $tokenValid = true): CsrfTokenManagerInterface
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->method('getToken')
            ->with(self::TOKEN_ID)
            ->willReturn(new CsrfToken(self::TOKEN_ID, self::VALID_TOKEN));
        $tokenManager->method('isTokenValid')
            ->willReturnCallback(function (CsrfToken $token) use ($tokenValid) {
                return $tokenValid && $token->getValue() === self::VALID_TOKEN;
            });

        return $tokenManager;
    }

    private function createHtmxRequest(
        string $method,
        ?string $csrfToken = null,
        bool $isHtmx = true,
    ): Request {
        $request = Request::create('/test', $method);

        if ($isHtmx) {
            $request->headers->set('HX-Request', 'true');
            $request->attributes->set(
                HtmxRequest::REQUEST_ATTRIBUTE_NAME,
                new HtmxRequest(isHtmx: true),
            );
        }

        if ($csrfToken !== null) {
            $request->headers->set(self::HEADER_NAME, $csrfToken);
        }

        return $request;
    }

    private function createRequestEvent(Request $request): RequestEvent
    {
        return new RequestEvent(
            $this->createMock(KernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );
    }

    /**
     * Tests that POST request with valid CSRF token passes validation.
     * Example: Delete button with htmx_csrf_headers()
     */
    public function testPostWithValidTokenPasses(): void
    {
        $subscriber = new CsrfValidationSubscriber($this->createTokenManager());
        $request = $this->createHtmxRequest('POST', self::VALID_TOKEN);
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);

        self::assertTrue(true);
    }

    /**
     * Tests that DELETE request with valid CSRF token passes validation.
     * Example: hx-delete="/items/1" with htmx_csrf_headers()
     */
    public function testDeleteWithValidTokenPasses(): void
    {
        $subscriber = new CsrfValidationSubscriber($this->createTokenManager());
        $request = $this->createHtmxRequest('DELETE', self::VALID_TOKEN);
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);

        self::assertTrue(true);
    }

    /**
     * Tests that PUT request with valid CSRF token passes validation.
     */
    public function testPutWithValidTokenPasses(): void
    {
        $subscriber = new CsrfValidationSubscriber($this->createTokenManager());
        $request = $this->createHtmxRequest('PUT', self::VALID_TOKEN);
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);

        self::assertTrue(true);
    }

    /**
     * Tests that PATCH request with valid CSRF token passes validation.
     */
    public function testPatchWithValidTokenPasses(): void
    {
        $subscriber = new CsrfValidationSubscriber($this->createTokenManager());
        $request = $this->createHtmxRequest('PATCH', self::VALID_TOKEN);
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);

        self::assertTrue(true);
    }

    /**
     * Tests that POST without CSRF token throws AccessDeniedHttpException.
     */
    public function testPostWithoutTokenThrowsException(): void
    {
        $subscriber = new CsrfValidationSubscriber($this->createTokenManager());
        $request = $this->createHtmxRequest('POST', null);
        $event = $this->createRequestEvent($request);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('CSRF token missing.');

        $subscriber->onKernelRequest($event);
    }

    /**
     * Tests that POST with invalid CSRF token throws AccessDeniedHttpException.
     */
    public function testPostWithInvalidTokenThrowsException(): void
    {
        $subscriber = new CsrfValidationSubscriber($this->createTokenManager());
        $request = $this->createHtmxRequest('POST', 'invalid-token');
        $event = $this->createRequestEvent($request);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('Invalid CSRF token.');

        $subscriber->onKernelRequest($event);
    }

    /**
     * Tests that DELETE without CSRF token throws AccessDeniedHttpException.
     */
    public function testDeleteWithoutTokenThrowsException(): void
    {
        $subscriber = new CsrfValidationSubscriber($this->createTokenManager());
        $request = $this->createHtmxRequest('DELETE', null);
        $event = $this->createRequestEvent($request);

        $this->expectException(AccessDeniedHttpException::class);
        $this->expectExceptionMessage('CSRF token missing.');

        $subscriber->onKernelRequest($event);
    }

    /**
     * Tests that GET requests are allowed without CSRF token (safe method).
     */
    public function testGetRequestSkipsValidation(): void
    {
        $subscriber = new CsrfValidationSubscriber($this->createTokenManager());
        $request = $this->createHtmxRequest('GET', null);
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);

        self::assertTrue(true);
    }

    /**
     * Tests that HEAD requests are allowed without CSRF token (safe method).
     */
    public function testHeadRequestSkipsValidation(): void
    {
        $subscriber = new CsrfValidationSubscriber($this->createTokenManager());
        $request = $this->createHtmxRequest('HEAD', null);
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);

        self::assertTrue(true);
    }

    /**
     * Tests that OPTIONS requests are allowed without CSRF token (safe method).
     */
    public function testOptionsRequestSkipsValidation(): void
    {
        $subscriber = new CsrfValidationSubscriber($this->createTokenManager());
        $request = $this->createHtmxRequest('OPTIONS', null);
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);

        self::assertTrue(true);
    }

    /**
     * Tests that non-htmx requests are skipped.
     */
    public function testNonHtmxRequestSkipsValidation(): void
    {
        $subscriber = new CsrfValidationSubscriber($this->createTokenManager());
        $request = $this->createHtmxRequest('POST', null, isHtmx: false);
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);

        self::assertTrue(true);
    }

    /**
     * Tests that CSRF validation can be disabled via configuration.
     */
    public function testDisabledValidationSkipsCheck(): void
    {
        $subscriber = new CsrfValidationSubscriber(
            $this->createTokenManager(),
            enabled: false,
        );
        $request = $this->createHtmxRequest('POST', null);
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);

        self::assertTrue(true);
    }

    /**
     * Tests custom token ID configuration.
     */
    public function testCustomTokenId(): void
    {
        $customTokenId = 'my-custom-token-id';
        $customToken = 'custom-token-value';

        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->method('isTokenValid')
            ->willReturnCallback(function (CsrfToken $token) use ($customTokenId, $customToken) {
                return $token->getId() === $customTokenId && $token->getValue() === $customToken;
            });

        $subscriber = new CsrfValidationSubscriber(
            $tokenManager,
            tokenId: $customTokenId,
        );
        $request = $this->createHtmxRequest('POST', $customToken);
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);

        self::assertTrue(true);
    }

    /**
     * Tests custom header name configuration.
     */
    public function testCustomHeaderName(): void
    {
        $customHeaderName = 'X-My-CSRF-Token';

        $subscriber = new CsrfValidationSubscriber(
            $this->createTokenManager(),
            headerName: $customHeaderName,
        );

        $request = $this->createHtmxRequest('POST', null);
        $request->headers->set($customHeaderName, self::VALID_TOKEN);
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);

        self::assertTrue(true);
    }

    /**
     * Tests custom safe methods configuration.
     */
    public function testCustomSafeMethods(): void
    {
        $subscriber = new CsrfValidationSubscriber(
            $this->createTokenManager(),
            safeMethods: ['GET', 'HEAD', 'OPTIONS', 'POST'],
        );

        $request = $this->createHtmxRequest('POST', null);
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);

        self::assertTrue(true);
    }

    /**
     * Tests that sub-requests are skipped.
     */
    public function testSubRequestSkipsValidation(): void
    {
        $subscriber = new CsrfValidationSubscriber($this->createTokenManager());
        $request = $this->createHtmxRequest('POST', null);

        $event = new RequestEvent(
            $this->createMock(KernelInterface::class),
            $request,
            HttpKernelInterface::SUB_REQUEST,
        );

        $subscriber->onKernelRequest($event);

        self::assertTrue(true);
    }

    /**
     * Tests the real-world scenario: delete button from csrf.md.
     */
    public function testDeleteButtonScenario(): void
    {
        $subscriber = new CsrfValidationSubscriber($this->createTokenManager());
        $request = $this->createHtmxRequest('DELETE', self::VALID_TOKEN);
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);

        self::assertTrue(true);
    }

    /**
     * Tests the real-world scenario: toggle action from csrf.md.
     */
    public function testToggleActionScenario(): void
    {
        $subscriber = new CsrfValidationSubscriber($this->createTokenManager());
        $request = $this->createHtmxRequest('POST', self::VALID_TOKEN);
        $event = $this->createRequestEvent($request);

        $subscriber->onKernelRequest($event);

        self::assertTrue(true);
    }
}
