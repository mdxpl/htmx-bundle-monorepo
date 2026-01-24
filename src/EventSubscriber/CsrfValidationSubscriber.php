<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\EventSubscriber;

use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfValidationSubscriber implements EventSubscriberInterface
{
    /**
     * @param list<string> $safeMethods
     */
    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly bool $enabled = true,
        private readonly string $tokenId = 'mdx-htmx',
        private readonly string $headerName = 'X-CSRF-Token',
        private readonly array $safeMethods = ['GET', 'HEAD', 'OPTIONS'],
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 8],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$this->enabled) {
            return;
        }

        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$this->isHtmxRequest($request)) {
            return;
        }

        if ($this->isSafeMethod($request->getMethod())) {
            return;
        }

        $tokenValue = $request->headers->get($this->headerName);

        if ($tokenValue === null) {
            throw new AccessDeniedHttpException('CSRF token missing.');
        }

        $token = new CsrfToken($this->tokenId, $tokenValue);

        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }
    }

    private function isHtmxRequest(\Symfony\Component\HttpFoundation\Request $request): bool
    {
        $htmxRequest = $request->attributes->get(HtmxRequest::REQUEST_ATTRIBUTE_NAME);

        return $htmxRequest instanceof HtmxRequest && $htmxRequest->isHtmx;
    }

    private function isSafeMethod(string $method): bool
    {
        return \in_array(strtoupper($method), $this->safeMethods, true);
    }
}
