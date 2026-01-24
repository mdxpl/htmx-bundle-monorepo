<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\EventSubscriber;

use Mdxpl\HtmxBundle\Attribute\HtmxOnly;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class HtmxOnlyAttributeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly bool $enabled = true,
        private readonly int $statusCode = Response::HTTP_NOT_FOUND,
        private readonly string $message = 'Not Found',
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController'],
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$this->enabled) {
            return;
        }

        if ($this->hasAttribute($event) && !$this->isHtmxRequest($event)) {
            throw new HttpException($this->statusCode, $this->message);
        }
    }

    private function hasAttribute(ControllerEvent $event): bool
    {
        $attributes = $event->getAttributes(HtmxOnly::class);

        return !empty($attributes);
    }

    private function isHtmxRequest(ControllerEvent $event): bool
    {
        return (bool) $event->getRequest()->attributes->get(HtmxRequest::REQUEST_ATTRIBUTE_NAME)?->isHtmx;
    }
}