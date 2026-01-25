<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\EventSubscriber;

use Mdxpl\HtmxBundle\Attribute\HtmxOnly;
use Mdxpl\HtmxBundle\EventSubscriber\HtmxOnlyAttributeSubscriber;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

class HtmxAttributeSubscriberTest extends TestCase
{
    public static function htmxOnlyCasesProvider(): array
    {
        return [
            'Not htmx request and annotated controller throws not found exception' => [false, true, true],
            'Htmx request and annotated controller returns success response' => [true, true, false],
            'Htmx request and not annotated controller returns success response' => [true, false, false],
            'Not htmx request and not annotated controller returns success response' => [false, false, false],
        ];
    }

    #[DataProvider('htmxOnlyCasesProvider')]
    public function testRequestWithHtmxWhenAttributeIsPresent(
        bool $htmxRequest,
        bool $annotatedController,
        bool $shouldThrowsException,
    ): void {
        if ($shouldThrowsException) {
            $this->expectException(HttpException::class);
        }

        $event = $this->createEvent($htmxRequest, $annotatedController);

        try {
            $subscriber = new HtmxOnlyAttributeSubscriber();
            $subscriber->onKernelController($event);

            self::assertTrue(true);
        } catch (HttpException $e) {
            $shouldThrowsException
                ? throw $e
                : $this->fail('Should not throw exception');
        }
    }

    private function createEvent(bool $withHtmxRequest, bool $withAnnotatedController): ControllerEvent
    {
        $kernel = $this->createMock(KernelInterface::class);
        $request = $this->createRequest($withHtmxRequest);
        $controller = $withAnnotatedController
            ? $this->createControllerWithAnnotation()
            : $this->createControllerWithoutAnnotation();

        return new ControllerEvent($kernel, $controller, $request, 1);
    }

    private function createRequest(bool $isHtmx): Request
    {
        $request = new Request();
        $request->attributes->set(HtmxRequest::REQUEST_ATTRIBUTE_NAME, new HtmxRequest($isHtmx));

        return $request;
    }

    private function createControllerWithAnnotation(): object
    {
        return new class() {
            #[HtmxOnly]
            public function __invoke(): HtmxResponse
            {
                return new HtmxResponse();
            }
        };
    }

    private function createControllerWithoutAnnotation(): object
    {
        return new class() {
            public function __invoke(): HtmxResponse
            {
                return new HtmxResponse();
            }
        };
    }

    public function testGetSubscribedEvents(): void
    {
        self::assertSame(
            [KernelEvents::CONTROLLER => ['onKernelController']],
            HtmxOnlyAttributeSubscriber::getSubscribedEvents(),
        );
    }

    public function testDisabledSubscriberDoesNotThrowException(): void
    {
        $event = $this->createEvent(withHtmxRequest: false, withAnnotatedController: true);

        $subscriber = new HtmxOnlyAttributeSubscriber(enabled: false);
        $subscriber->onKernelController($event);

        self::assertTrue(true);
    }

    public function testCustomStatusCodeAndMessage(): void
    {
        $event = $this->createEvent(withHtmxRequest: false, withAnnotatedController: true);

        $subscriber = new HtmxOnlyAttributeSubscriber(
            enabled: true,
            statusCode: 403,
            message: 'Forbidden',
        );

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Forbidden');

        try {
            $subscriber->onKernelController($event);
        } catch (HttpException $e) {
            self::assertEquals(403, $e->getStatusCode());
            throw $e;
        }
    }
}
