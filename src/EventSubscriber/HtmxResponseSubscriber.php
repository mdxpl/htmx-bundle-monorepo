<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\EventSubscriber;

use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\ResponseFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class HtmxResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(private ResponseFactory $responseFactory) { }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['onKernelView', 0],
        ];
    }

    public function onKernelView(ViewEvent $event): void
    {
        $result = $event->getControllerResult();
        if ($result instanceof HtmxResponse) {
            $event->setResponse($this->responseFactory->create($result));
        }
    }
}