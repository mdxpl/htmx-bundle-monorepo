<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response;

use Exception;
use Mdxpl\HtmxBundle\Exception\ReservedViewParamCannotBeOverriddenException;
use Mdxpl\HtmxBundle\Response\Headers\PushUrl;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Mdxpl\HtmxBundle\Response\Result;
use Mdxpl\HtmxBundle\Response\Swap\Modifiers\TimingSwap;
use Mdxpl\HtmxBundle\Response\Swap\Modifiers\Transition;
use Mdxpl\HtmxBundle\Response\Swap\SwapStyle;
use Mdxpl\HtmxBundle\Response\View\View;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class HtmxResponseBuilderTest extends TestCase
{
    public function testInitForNonHtmxRequest(): void
    {
        $builder = HtmxResponseBuilder::create(false);

        $htmxResponse = $builder->build();

        Assert::assertCount(0, $htmxResponse->views);
        Assert::assertCount(0, $htmxResponse->headers);
        Assert::assertEquals(204, $htmxResponse->responseCode);
    }

    public function testInitForHtmxRequest(): void
    {
        $builder = HtmxResponseBuilder::create(true);

        $htmxResponse = $builder->build();

        Assert::assertCount(0, $htmxResponse->views);
        Assert::assertCount(0, $htmxResponse->headers);
        Assert::assertEquals(204, $htmxResponse->responseCode);
    }

    public function testWithViewAddsNewView(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->view('template.html.twig');

        $htmxResponse = $builder->build();

        Assert::assertCount(1, $htmxResponse->views);
        Assert::assertCount(0, $htmxResponse->headers);
        Assert::assertEquals('template.html.twig', $htmxResponse->views->first()->template);
    }

    public function testWithNoContent(): void
    {
        $builder = HtmxResponseBuilder::create(true)->noContent();

        $htmxResponse = $builder->build();

        Assert::assertCount(0, $htmxResponse->views);
        Assert::assertCount(0, $htmxResponse->headers);
        Assert::assertEquals(204, $htmxResponse->responseCode);
    }

    public function testWithResponseCode(): void
    {
        $builder = HtmxResponseBuilder::create(true)->responseCode(201, Result::SUCCESS);

        $htmxResponse = $builder->build();

        Assert::assertCount(0, $htmxResponse->views);
        Assert::assertCount(0, $htmxResponse->headers);
        Assert::assertEquals(201, $htmxResponse->responseCode);
    }

    public function testWithRedirect(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->redirect('https://example.com');

        $htmxResponse = $builder->build();

        Assert::assertEquals('HX-Redirect', $htmxResponse->headers->first()->getType()->value);
        Assert::assertEquals('https://example.com', $htmxResponse->headers->first()->getValue());
    }

    public function testWithLocation(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->location('https://example.com');

        $htmxResponse = $builder->build();

        Assert::assertEquals('HX-Location', $htmxResponse->headers->first()->getType()->value);
        Assert::assertEquals('https://example.com', $htmxResponse->headers->first()->getValue());
    }

    public function testWithPushUrl(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->pushUrl('/test');

        $htmxResponse = $builder->build();

        Assert::assertEquals('HX-Push-Url', $htmxResponse->headers->first()->getType()->value);
        Assert::assertEquals('/test', $htmxResponse->headers->first()->getValue());
    }

    public function testWithRefresh(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->refresh();

        $htmxResponse = $builder->build();

        Assert::assertEquals('HX-Refresh', $htmxResponse->headers->first()->getType()->value);
    }

    public function testWithReplaceUrl(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->replaceUrl('/test');

        $htmxResponse = $builder->build();

        Assert::assertEquals('HX-Replace-Url', $htmxResponse->headers->first()->getType()->value);
        Assert::assertEquals('/test', $htmxResponse->headers->first()->getValue());
    }

    public function testWithReselect(): void
    {
        $cssSelector = '.some-class';
        $builder = HtmxResponseBuilder::create(true)
            ->reselect($cssSelector);

        $htmxResponse = $builder->build();

        Assert::assertEquals('HX-Reselect', $htmxResponse->headers->first()->getType()->value);
        Assert::assertEquals($cssSelector, $htmxResponse->headers->first()->getValue());
    }

    public function testWithTriggerEventsWithoutData(): void
    {
        $events = 'event1,event2';
        $builder = HtmxResponseBuilder::create(true)
            ->trigger($events);

        $htmxResponse = $builder->build();

        Assert::assertEquals('HX-Trigger', $htmxResponse->headers->first()->getType()->value);
        Assert::assertEquals($events, $htmxResponse->headers->first()->getValue());
    }

    public function testWithTriggerForEventsWithData(): void
    {
        $eventsWithData = [
            'event1' => 'data1',
            'event2' => ['key' => 'value', 'key2' => 'value2'],
        ];

        $expected = '{"event1":"data1","event2":{"key":"value","key2":"value2"}}';

        $builder = HtmxResponseBuilder::create(true)
            ->trigger($eventsWithData);

        $htmxResponse = $builder->build();

        Assert::assertEquals('HX-Trigger', $htmxResponse->headers->first()->getType()->value);
        Assert::assertEquals($expected, $htmxResponse->headers->first()->getValue());
    }

    public function testWithViewParamThrowsExceptionOnReservedParam(): void
    {
        $this->expectException(ReservedViewParamCannotBeOverriddenException::class);

        HtmxResponseBuilder::create(true)
            ->view('template.html.twig', [
                View::RESULT_VIEW_PARAM_NAME => 'value',
            ]);
    }

    public function testChainAllMethodsDoesNotCrash(): void
    {
        try {
            HtmxResponseBuilder::create(true)
                ->view('template.html.twig', ['test' => 'ok'], 'block')
                ->success()
                ->success()
                ->failure()
                ->header(new PushUrl('https://push.example.com'))
                ->location('https://example.comm')
                ->pushUrl('https://example.com')
                ->redirect('https://example.com')
                ->refresh()
                ->replaceUrl('https://example.com')
                ->reselect('.css-selector')
                ->retarget('.retarget-selector')
                ->trigger(['event1' => 'data1', 'event2' => 'data2'])
                ->triggerAfterSettle('settleEvent')
                ->triggerAfterSwap('swapEvent')
                ->withReswap(SwapStyle::OUTER_HTML, new Transition(), new TimingSwap(500))
                ->build();

            $this->assertTrue(true, "Chaining all methods did not result in a crash or exception.");
        } catch (Exception $e) {
            $this->fail("Chaining all methods resulted in an exception: " . $e->getMessage());
        }
    }

}
