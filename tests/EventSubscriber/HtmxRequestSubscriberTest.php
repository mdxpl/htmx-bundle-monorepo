<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\EventSubscriber;

use Mdxpl\HtmxBundle\EventSubscriber\HtmxRequestSubscriber;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Request\HtmxRequestHeaderType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class HtmxRequestSubscriberTest extends TestCase
{
    public function testOnKernelRequestWithHtmxHeader(): void
    {
        $request = new Request();
        $request->headers->set(HtmxRequestHeaderType::REQUEST->value, 'true');

        $event = $this->createMock(RequestEvent::class);
        $event->method('isMainRequest')->willReturn(true);
        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $subscriber = new HtmxRequestSubscriber();
        $subscriber->onKernelRequest($event);

        $this->assertInstanceOf(
            HtmxRequest::class,
            $request->attributes->get(HtmxRequest::REQUEST_ATTRIBUTE_NAME),
        );
    }

    public function testOnKernelRequestWithoutHtmxHeader(): void
    {
        $request = new Request();
        $event = $this->createMock(RequestEvent::class);
        $event->method('isMainRequest')->willReturn(true);
        $event->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $subscriber = new HtmxRequestSubscriber();
        $subscriber->onKernelRequest($event);

        $this->assertTrue($request->attributes->has(HtmxRequest::REQUEST_ATTRIBUTE_NAME));
        $this->assertFalse($request->attributes->get(HtmxRequest::REQUEST_ATTRIBUTE_NAME)->isHtmx);
    }

    public function testOnKernelRequestSkipsSubRequest(): void
    {
        $request = new Request();
        $request->headers->set(HtmxRequestHeaderType::REQUEST->value, 'true');

        $event = $this->createMock(RequestEvent::class);
        $event->method('isMainRequest')->willReturn(false);
        $event->expects($this->never())->method('getRequest');

        $subscriber = new HtmxRequestSubscriber();
        $subscriber->onKernelRequest($event);

        $this->assertFalse($request->attributes->has(HtmxRequest::REQUEST_ATTRIBUTE_NAME));
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [KernelEvents::REQUEST => ['onKernelRequest']],
            HtmxRequestSubscriber::getSubscribedEvents(),
        );
    }
}
