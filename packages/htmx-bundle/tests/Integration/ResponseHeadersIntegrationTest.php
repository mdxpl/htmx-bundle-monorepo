<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Integration;

use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Mdxpl\HtmxBundle\Response\Swap\Modifiers\FocusScroll;
use Mdxpl\HtmxBundle\Response\Swap\Modifiers\ScrollingDirection;
use Mdxpl\HtmxBundle\Response\Swap\Modifiers\ScrollingScroll;
use Mdxpl\HtmxBundle\Response\Swap\Modifiers\TimingSwap;
use Mdxpl\HtmxBundle\Response\Swap\Modifiers\Transition;
use Mdxpl\HtmxBundle\Response\Swap\SwapStyle;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for htmx response headers.
 *
 * Tests the full flow from HtmxResponseBuilder to HTTP response headers.
 */
class ResponseHeadersIntegrationTest extends TestCase
{
    use TwigIntegrationTestTrait;

    protected function setUp(): void
    {
        $this->setUpTwig();
    }

    public function testRedirectHeader(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->redirect('/dashboard')
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        self::assertSame('/dashboard', $response->headers->get('HX-Redirect'));
    }

    public function testLocationHeader(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->location('/new-page')
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        self::assertSame('/new-page', $response->headers->get('HX-Location'));
    }

    public function testPushUrlHeader(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->pushUrl('/updated-url')
            ->viewBlock('simple_page.html.twig', 'pageContentPartial', [
                'page' => ['name' => 'Test', 'description' => 'Test'],
            ])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        self::assertSame('/updated-url', $response->headers->get('HX-Push-Url'));
    }

    public function testReplaceUrlHeader(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->replaceUrl('/replaced-url')
            ->viewBlock('simple_page.html.twig', 'pageContentPartial', [
                'page' => ['name' => 'Test', 'description' => 'Test'],
            ])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        self::assertSame('/replaced-url', $response->headers->get('HX-Replace-Url'));
    }

    public function testRefreshHeader(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->refresh()
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        self::assertSame('true', $response->headers->get('HX-Refresh'));
    }

    public function testRetargetHeader(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->retarget('#new-target')
            ->viewBlock('simple_page.html.twig', 'pageContentPartial', [
                'page' => ['name' => 'Test', 'description' => 'Test'],
            ])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        self::assertSame('#new-target', $response->headers->get('HX-Retarget'));
    }

    public function testReselectHeader(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->reselect('.content-area')
            ->viewBlock('simple_page.html.twig', 'pageContentPartial', [
                'page' => ['name' => 'Test', 'description' => 'Test'],
            ])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        self::assertSame('.content-area', $response->headers->get('HX-Reselect'));
    }

    public function testSimpleTriggerHeader(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->trigger('itemDeleted')
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        self::assertSame('itemDeleted', $response->headers->get('HX-Trigger'));
    }

    public function testTriggerWithMultipleEvents(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->trigger(['event1', 'event2', 'event3'])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $trigger = $response->headers->get('HX-Trigger');
        self::assertStringContainsString('event1', $trigger);
        self::assertStringContainsString('event2', $trigger);
        self::assertStringContainsString('event3', $trigger);
    }

    public function testTriggerWithEventData(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->trigger(['showMessage' => ['message' => 'Item saved', 'type' => 'success']])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $trigger = $response->headers->get('HX-Trigger');
        self::assertJson($trigger);
        $decoded = json_decode($trigger, true);
        self::assertArrayHasKey('showMessage', $decoded);
        self::assertSame('Item saved', $decoded['showMessage']['message']);
        self::assertSame('success', $decoded['showMessage']['type']);
    }

    public function testTriggerAfterSwapHeader(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->triggerAfterSwap('animationComplete')
            ->viewBlock('simple_page.html.twig', 'pageContentPartial', [
                'page' => ['name' => 'Test', 'description' => 'Test'],
            ])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        self::assertSame('animationComplete', $response->headers->get('HX-Trigger-After-Swap'));
    }

    public function testTriggerAfterSettleHeader(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->triggerAfterSettle('contentSettled')
            ->viewBlock('simple_page.html.twig', 'pageContentPartial', [
                'page' => ['name' => 'Test', 'description' => 'Test'],
            ])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        self::assertSame('contentSettled', $response->headers->get('HX-Trigger-After-Settle'));
    }

    public function testReswapWithStyleOnly(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->withReswap(SwapStyle::OUTER_HTML)
            ->viewBlock('simple_page.html.twig', 'pageContentPartial', [
                'page' => ['name' => 'Test', 'description' => 'Test'],
            ])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        self::assertSame('outerHTML', $response->headers->get('HX-Reswap'));
    }

    public function testReswapWithModifiers(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->withReswap(
                SwapStyle::INNER_HTML,
                new TimingSwap(500),
                new Transition(),
                new FocusScroll(false),
            )
            ->viewBlock('simple_page.html.twig', 'pageContentPartial', [
                'page' => ['name' => 'Test', 'description' => 'Test'],
            ])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $reswap = $response->headers->get('HX-Reswap');
        self::assertStringContainsString('innerHTML', $reswap);
        self::assertStringContainsString('swap:500ms', $reswap);
        self::assertStringContainsString('transition:true', $reswap);
        self::assertStringContainsString('focus-scroll:false', $reswap);
    }

    public function testReswapWithScrollModifier(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->withReswap(
                SwapStyle::BEFORE_END,
                new ScrollingScroll(ScrollingDirection::TOP, '#scroll-target'),
            )
            ->viewBlock('simple_page.html.twig', 'pageContentPartial', [
                'page' => ['name' => 'Test', 'description' => 'Test'],
            ])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $reswap = $response->headers->get('HX-Reswap');
        self::assertStringContainsString('beforeend', $reswap);
        self::assertStringContainsString('scroll:#scroll-target:top', $reswap);
    }

    public function testMultipleHeadersCombined(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->pushUrl('/new-url')
            ->retarget('#main-content')
            ->withReswap(SwapStyle::OUTER_HTML)
            ->trigger('contentUpdated')
            ->viewBlock('simple_page.html.twig', 'pageContentPartial', [
                'page' => ['name' => 'Test', 'description' => 'Test'],
            ])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        self::assertSame('/new-url', $response->headers->get('HX-Push-Url'));
        self::assertSame('#main-content', $response->headers->get('HX-Retarget'));
        self::assertSame('outerHTML', $response->headers->get('HX-Reswap'));
        self::assertSame('contentUpdated', $response->headers->get('HX-Trigger'));
        self::assertSame(200, $response->getStatusCode());
    }

    public function testHeadersWithNoContentResponse(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->noContent()
            ->trigger('itemDeleted')
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        self::assertSame(204, $response->getStatusCode());
        self::assertSame('itemDeleted', $response->headers->get('HX-Trigger'));
        self::assertEmpty($response->getContent());
    }
}
