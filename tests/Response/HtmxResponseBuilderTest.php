<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response;

use Exception;
use Mdxpl\HtmxBundle\Exception\ReservedViewParamCannotBeOverriddenException;
use Mdxpl\HtmxBundle\Response\Headers\Location;
use Mdxpl\HtmxBundle\Response\Headers\PushUrl;
use Mdxpl\HtmxBundle\Response\Headers\Refresh;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Mdxpl\HtmxBundle\Response\Swap\Modifiers\TimingSwap;
use Mdxpl\HtmxBundle\Response\Swap\Modifiers\Transition;
use Mdxpl\HtmxBundle\Response\Swap\SwapStyle;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class HtmxResponseBuilderTest extends TestCase
{
    public function testInitForNonHtmxRequest(): void
    {
        $builder = HtmxResponseBuilder::create(false);

        $htmxResponse = $builder->build();

        Assert::assertNull($htmxResponse->template);
        Assert::assertNull($htmxResponse->blockName);
        Assert::assertEquals(200, $htmxResponse->responseCode);
    }

    public function testInitForHtmxRequest(): void
    {
        $builder = HtmxResponseBuilder::create(true);

        $htmxResponse = $builder->build();

        Assert::assertNull($htmxResponse->template);
        Assert::assertNull($htmxResponse->blockName);
        Assert::assertEquals(200, $htmxResponse->responseCode);
    }

    public function testWithViewParamAddsNewParam(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->withViewParam('param1', 'value1');

        $htmxResponse = $builder->build();

        Assert::assertIsArray($htmxResponse->viewParams);
        Assert::assertArrayHasKey('param1', $htmxResponse->viewParams);
        Assert::assertContains('value1', $htmxResponse->viewParams);
    }

    public function testWithViewDataReplacesAllParams(): void
    {
        $builder = HtmxResponseBuilder::create(true, 'template.html.twig')
            ->withViewParam('param1', 'value1')
            ->withViewParams(['param2' => 'value2']);

        $htmxResponse = $builder->build();

        Assert::assertArrayNotHasKey('param1', $htmxResponse->viewParams);
        Assert::assertArrayHasKey('param2', $htmxResponse->viewParams);
        Assert::assertContains('value2', $htmxResponse->viewParams);
    }

    public function testWithTemplateReplacesTemplate(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->withTemplate('new-template.html.twig');

        $htmxResponse = $builder->build();

        Assert::assertEquals($htmxResponse->template, 'new-template.html.twig');
    }

    public function testWithBlockSetsBlockName(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->withBlock('newBlockName');

        $htmxResponse = $builder->build();

        Assert::assertSame('newBlockName', $htmxResponse->blockName);
    }

    public function testWithRedirect(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->withRedirect('https://example.com');

        $htmxResponse = $builder->build();

        Assert::assertEquals('HX-Redirect', $htmxResponse->headers['HX-Redirect']?->getType()->value);
        Assert::assertEquals('https://example.com', $htmxResponse->headers['HX-Redirect']?->getValue());
    }

    public function testWithLocation(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->withLocation('https://example.com');

        $htmxResponse = $builder->build();

        Assert::assertEquals('HX-Location', $htmxResponse->headers['HX-Location']?->getType()->value);
        Assert::assertEquals('https://example.com', $htmxResponse->headers['HX-Location']?->getValue());
    }

    public function testWithPushUrl(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->withPushUrl('/test');

        $htmxResponse = $builder->build();

        Assert::assertEquals('HX-Push-Url', $htmxResponse->headers['HX-Push-Url']?->getType()->value);
        Assert::assertEquals('/test', $htmxResponse->headers['HX-Push-Url']?->getValue());
    }

    public function testWithRefresh(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->withRefresh();

        $htmxResponse = $builder->build();

        Assert::assertEquals('HX-Refresh', $htmxResponse->headers['HX-Refresh']->getType()->value);
    }

    public function testWithReplaceUrl(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->withReplaceUrl('/test');

        $htmxResponse = $builder->build();

        Assert::assertEquals('HX-Replace-Url', $htmxResponse->headers['HX-Replace-Url']->getType()->value);
        Assert::assertEquals('/test', $htmxResponse->headers['HX-Replace-Url']->getValue());
    }

    public function testWithReselect(): void
    {
        $cssSelector = '.some-class';
        $builder = HtmxResponseBuilder::create(true)
            ->withReselect($cssSelector);

        $htmxResponse = $builder->build();

        Assert::assertEquals('HX-Reselect', $htmxResponse->headers['HX-Reselect']->getType()->value);
        Assert::assertEquals($cssSelector, $htmxResponse->headers['HX-Reselect']->getValue());
    }

    public function testWithTriggerEventsWithoutData(): void
    {
        $events = 'event1,event2';
        $builder = HtmxResponseBuilder::create(true)
            ->withTrigger($events);

        $htmxResponse = $builder->build();

        Assert::assertEquals('HX-Trigger', $htmxResponse->headers['HX-Trigger']->getType()->value);
        Assert::assertEquals($events, $htmxResponse->headers['HX-Trigger']->getValue());
    }

    public function testWithTriggerForEventsWithData(): void
    {
        $eventsWithData = [
            'event1' => 'data1',
            'event2' => ['key' => 'value', 'key2' => 'value2'],
        ];

        $expected = '{"event1":"data1","event2":{"key":"value","key2":"value2"}}';

        $builder = HtmxResponseBuilder::create(true)
            ->withTrigger($eventsWithData);

        $htmxResponse = $builder->build();

        Assert::assertEquals('HX-Trigger', $htmxResponse->headers['HX-Trigger']->getType()->value);
        Assert::assertEquals($expected, $htmxResponse->headers['HX-Trigger']->getValue());
    }

    public function testWithViewParamThrowsExceptionOnReservedParam(): void
    {
        $this->expectException(ReservedViewParamCannotBeOverriddenException::class);

        HtmxResponseBuilder::create(true)
            ->withViewParam(HtmxResponse::RESULT_VIEW_PARAM_NAME, 'value');
    }

    public function testChainAllMethodsDoesNotCrash(): void
    {
        try {
            $builder = HtmxResponseBuilder::create(true)
                ->withTemplate('template.html.twig')
                ->withBlock('custom')
                ->withResponseCode(200)
                ->withSuccess()
                ->withFailure()
                ->withHeaders(new Location('https://example.com'), new Refresh())
                ->withHeader(new PushUrl('https://push.example.com'))
                ->withViewParams(['custom_param' => 'value'])
                ->withViewParam('another_param', 'another_value')
                ->withLocation('https://example.comm')
                ->withPushUrl('https://example.com')
                ->withRedirect('https://example.com')
                ->withRefresh()
                ->withReplaceUrl('https://example.com')
                ->withReselect('.css-selector')
                ->withRetarget('.retarget-selector')
                ->withTrigger(['event1' => 'data1', 'event2' => 'data2'])
                ->withTriggerAfterSettle('settleEvent')
                ->withTriggerAfterSwap('swapEvent')
                ->withReswap(SwapStyle::OUTER_HTML, new Transition(), new TimingSwap(500))
                ->build();

            $this->assertInstanceOf(HtmxResponse::class, $builder);
            $this->assertTrue(true, "Chaining all methods did not result in a crash or exception.");
        } catch (Exception $e) {
            $this->fail("Chaining all methods resulted in an exception: " . $e->getMessage());
        }
    }

}
