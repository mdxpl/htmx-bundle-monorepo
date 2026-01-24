<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Integration;

use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for out-of-band (OOB) swaps.
 *
 * htmx supports returning multiple elements in a single response for
 * OOB swaps, allowing updates to multiple parts of the page with a single request.
 *
 * @see https://htmx.org/attributes/hx-swap-oob/
 */
class OutOfBandSwapsIntegrationTest extends TestCase
{
    use TwigIntegrationTestTrait;

    protected function setUp(): void
    {
        $this->setUpTwig();
    }

    /**
     * Tests rendering main content with an OOB notification.
     * Common pattern: update main content + show a toast notification.
     */
    public function testMainContentWithOobNotification(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->viewBlock('form.html.twig', 'successComponent')
            ->viewBlock('oob_notification.html.twig', 'notification', [
                'message' => 'Item saved successfully!',
                'type' => 'success',
            ])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();

        $this->assertStringContainsString('Great success!', $content);
        $this->assertStringContainsString('hx-swap-oob="true"', $content);
        $this->assertStringContainsString('Item saved successfully!', $content);
        $this->assertStringContainsString('alert-success', $content);
    }

    /**
     * Tests rendering main content with multiple OOB elements.
     * Common pattern: update content + notification + counter.
     */
    public function testMainContentWithMultipleOobElements(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->viewBlock('form.html.twig', 'successComponent')
            ->viewBlock('oob_notification.html.twig', 'notification', [
                'message' => 'Item added to cart!',
                'type' => 'info',
            ])
            ->viewBlock('oob_counter.html.twig', 'counter', [
                'count' => 5,
            ])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();

        $this->assertStringContainsString('Great success!', $content);
        $this->assertStringContainsString('Item added to cart!', $content);
        $this->assertStringContainsString('id="cart-counter"', $content);
        $this->assertStringContainsString('>5<', $content);

        $this->assertSame(2, substr_count($content, 'hx-swap-oob="true"'));
    }

    /**
     * Tests that views are separated by double newlines.
     */
    public function testViewsSeparatedByDoubleNewlines(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->viewBlock('form.html.twig', 'successComponent')
            ->viewBlock('oob_notification.html.twig', 'notification', [
                'message' => 'Test',
                'type' => 'info',
            ])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);
        $content = $response->getContent();

        $this->assertStringContainsString(PHP_EOL . PHP_EOL, $content);
    }

    /**
     * Tests rendering only OOB elements (no main content).
     * Useful for updating multiple page parts without main swap.
     */
    public function testOnlyOobElements(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->viewBlock('oob_notification.html.twig', 'notification', [
                'message' => 'Background task completed',
                'type' => 'success',
            ])
            ->viewBlock('oob_counter.html.twig', 'counter', [
                'count' => 10,
            ])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();

        $this->assertStringContainsString('Background task completed', $content);
        $this->assertStringContainsString('>10<', $content);
        $this->assertStringNotContainsString('Great success!', $content);
    }

    /**
     * Tests failure response with OOB error notification.
     */
    public function testFailureWithOobErrorNotification(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->failure()
            ->viewBlock('form.html.twig', 'failureComponent', [
                'formData' => ['name' => 'A'],
                'errors' => ['Name is too short'],
            ])
            ->viewBlock('oob_notification.html.twig', 'notification', [
                'message' => 'Please fix the errors',
                'type' => 'danger',
            ])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $this->assertSame(422, $response->getStatusCode());
        $content = $response->getContent();

        $this->assertStringContainsString('Fix the errors in the form!', $content);
        $this->assertStringContainsString('Please fix the errors', $content);
        $this->assertStringContainsString('alert-danger', $content);
    }

    /**
     * Tests clearing views removes all previously added views.
     */
    public function testClearViewsRemovesAllViews(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->viewBlock('form.html.twig', 'successComponent')
            ->viewBlock('oob_notification.html.twig', 'notification', [
                'message' => 'Should not appear',
                'type' => 'info',
            ])
            ->clearViews()
            ->viewBlock('form.html.twig', 'formComponent', ['formData' => [], 'errors' => []])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);
        $content = $response->getContent();

        $this->assertStringNotContainsString('Great success!', $content);
        $this->assertStringNotContainsString('Should not appear', $content);
        $this->assertStringContainsString('<form', $content);
    }
}
