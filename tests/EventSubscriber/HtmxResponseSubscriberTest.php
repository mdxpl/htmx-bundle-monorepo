<?php

namespace Mdxpl\HtmxBundle\Tests\EventSubscriber;

use Mdxpl\HtmxBundle\EventSubscriber\HtmxResponseSubscriber;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\ResponseFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class HtmxResponseSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [KernelEvents::VIEW => ['onKernelView', 0]],
            HtmxResponseSubscriber::getSubscribedEvents(),
        );
    }

    public function testOnKernelView(): void
    {
        $event = new ViewEvent(
            $this->createMock(KernelInterface::class),
            new Request(),
            1,
            new HtmxResponse(200)
        );

        $twig = $this->createMock(Environment::class);
        $subscriber = new HtmxResponseSubscriber(new ResponseFactory($twig));
        $subscriber->onKernelView($event);

        Assert::assertInstanceOf(Response::class, $event->getResponse());
    }

}
