<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\EventSubscriber;

use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Request\HtmxRequestHeaderType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class HtmxRequestSubscriber implements EventSubscriberInterface
{
    public const REQUEST_ATTRIBUTE_NAME = 'htmxRequest';

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->headers->has(HtmxRequestHeaderType::REQUEST->value)) {

            return;
        }

        $request->attributes->set(
            self::REQUEST_ATTRIBUTE_NAME,
            HtmxRequest::createFromSymfonyHttpRequest($request),
        );
    }
}
