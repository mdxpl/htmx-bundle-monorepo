<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Form\Htmx;

use Mdxpl\HtmxBundle\Form\Htmx\HtmxOptions;
use Mdxpl\HtmxBundle\Form\Htmx\Route;
use Mdxpl\HtmxBundle\Form\Htmx\SwapStyle;
use Mdxpl\HtmxBundle\Form\Htmx\Trigger\Trigger;
use PHPUnit\Framework\TestCase;

class HtmxOptionsTest extends TestCase
{
    public function testCreate(): void
    {
        $options = HtmxOptions::create();

        self::assertInstanceOf(HtmxOptions::class, $options);
        self::assertSame([], $options->toArray());
    }

    // ==========================================
    // HTTP Methods (URL)
    // ==========================================

    public function testGet(): void
    {
        $options = HtmxOptions::create()->get('/search');

        self::assertSame(['get' => '/search'], $options->toArray());
    }

    public function testPost(): void
    {
        $options = HtmxOptions::create()->post('/submit');

        self::assertSame(['post' => '/submit'], $options->toArray());
    }

    public function testPut(): void
    {
        $options = HtmxOptions::create()->put('/update');

        self::assertSame(['put' => '/update'], $options->toArray());
    }

    public function testPatch(): void
    {
        $options = HtmxOptions::create()->patch('/partial-update');

        self::assertSame(['patch' => '/partial-update'], $options->toArray());
    }

    public function testDelete(): void
    {
        $options = HtmxOptions::create()->delete('/remove');

        self::assertSame(['delete' => '/remove'], $options->toArray());
    }

    // ==========================================
    // HTTP Methods (Route)
    // ==========================================

    public function testGetRoute(): void
    {
        $options = HtmxOptions::create()->getRoute('app_search');

        $result = $options->toArray();
        self::assertArrayHasKey('get', $result);
        self::assertInstanceOf(Route::class, $result['get']);
        self::assertSame('app_search', $result['get']->name);
        self::assertSame([], $result['get']->params);
    }

    public function testGetRouteWithParams(): void
    {
        $options = HtmxOptions::create()->getRoute('app_search', ['query' => 'foo']);

        $result = $options->toArray();
        self::assertInstanceOf(Route::class, $result['get']);
        self::assertSame('app_search', $result['get']->name);
        self::assertSame(['query' => 'foo'], $result['get']->params);
    }

    public function testPostRoute(): void
    {
        $options = HtmxOptions::create()->postRoute('app_validate', ['field' => 'email']);

        $result = $options->toArray();
        self::assertInstanceOf(Route::class, $result['post']);
        self::assertSame('app_validate', $result['post']->name);
        self::assertSame(['field' => 'email'], $result['post']->params);
    }

    public function testPutRoute(): void
    {
        $options = HtmxOptions::create()->putRoute('app_update', ['id' => 1]);

        $result = $options->toArray();
        self::assertInstanceOf(Route::class, $result['put']);
    }

    public function testPatchRoute(): void
    {
        $options = HtmxOptions::create()->patchRoute('app_patch', ['id' => 1]);

        $result = $options->toArray();
        self::assertInstanceOf(Route::class, $result['patch']);
    }

    public function testDeleteRoute(): void
    {
        $options = HtmxOptions::create()->deleteRoute('app_delete', ['id' => 1]);

        $result = $options->toArray();
        self::assertInstanceOf(Route::class, $result['delete']);
    }

    // ==========================================
    // Core Attributes
    // ==========================================

    public function testTriggerWithString(): void
    {
        $options = HtmxOptions::create()->trigger('click');

        self::assertSame(['trigger' => 'click'], $options->toArray());
    }

    public function testTriggerWithTriggerObject(): void
    {
        $options = HtmxOptions::create()->trigger(Trigger::keyup()->changed()->delay(300));

        self::assertSame(['trigger' => 'keyup changed delay:300ms'], $options->toArray());
    }

    public function testTarget(): void
    {
        $options = HtmxOptions::create()->target('#results');

        self::assertSame(['target' => '#results'], $options->toArray());
    }

    public function testSwapWithString(): void
    {
        $options = HtmxOptions::create()->swap('innerHTML');

        self::assertSame(['swap' => 'innerHTML'], $options->toArray());
    }

    public function testSwapWithEnum(): void
    {
        $options = HtmxOptions::create()->swap(SwapStyle::OuterHTML);

        self::assertSame(['swap' => 'outerHTML'], $options->toArray());
    }

    public function testIndicator(): void
    {
        $options = HtmxOptions::create()->indicator('#spinner');

        self::assertSame(['indicator' => '#spinner'], $options->toArray());
    }

    // ==========================================
    // Request Modifiers
    // ==========================================

    public function testInclude(): void
    {
        $options = HtmxOptions::create()->include('[name^="filter"]');

        self::assertSame(['include' => '[name^="filter"]'], $options->toArray());
    }

    public function testValsWithArray(): void
    {
        $options = HtmxOptions::create()->vals(['key' => 'value']);

        self::assertSame(['vals' => ['key' => 'value']], $options->toArray());
    }

    public function testValsWithString(): void
    {
        $options = HtmxOptions::create()->vals('js:getValues()');

        self::assertSame(['vals' => 'js:getValues()'], $options->toArray());
    }

    public function testParams(): void
    {
        $options = HtmxOptions::create()->params('*');

        self::assertSame(['params' => '*'], $options->toArray());
    }

    public function testHeaders(): void
    {
        $options = HtmxOptions::create()->headers(['X-Custom' => 'value']);

        self::assertSame(['headers' => ['X-Custom' => 'value']], $options->toArray());
    }

    // ==========================================
    // Response Handling
    // ==========================================

    public function testSelect(): void
    {
        $options = HtmxOptions::create()->select('.content');

        self::assertSame(['select' => '.content'], $options->toArray());
    }

    public function testSelectOob(): void
    {
        $options = HtmxOptions::create()->selectOob('#sidebar');

        self::assertSame(['select-oob' => '#sidebar'], $options->toArray());
    }

    // ==========================================
    // User Interaction
    // ==========================================

    public function testConfirm(): void
    {
        $options = HtmxOptions::create()->confirm('Are you sure?');

        self::assertSame(['confirm' => 'Are you sure?'], $options->toArray());
    }

    public function testPrompt(): void
    {
        $options = HtmxOptions::create()->prompt('Enter value:');

        self::assertSame(['prompt' => 'Enter value:'], $options->toArray());
    }

    // ==========================================
    // URL/History
    // ==========================================

    public function testPushUrlTrue(): void
    {
        $options = HtmxOptions::create()->pushUrl();

        self::assertSame(['push-url' => true], $options->toArray());
    }

    public function testPushUrlWithUrl(): void
    {
        $options = HtmxOptions::create()->pushUrl('/new-url');

        self::assertSame(['push-url' => '/new-url'], $options->toArray());
    }

    public function testReplaceUrl(): void
    {
        $options = HtmxOptions::create()->replaceUrl('/replaced');

        self::assertSame(['replace-url' => '/replaced'], $options->toArray());
    }

    // ==========================================
    // Synchronization
    // ==========================================

    public function testSync(): void
    {
        $options = HtmxOptions::create()->sync('closest form:abort');

        self::assertSame(['sync' => 'closest form:abort'], $options->toArray());
    }

    public function testDisabledElt(): void
    {
        $options = HtmxOptions::create()->disabledElt('button');

        self::assertSame(['disabled-elt' => 'button'], $options->toArray());
    }

    // ==========================================
    // Event Handlers
    // ==========================================

    public function testOn(): void
    {
        $options = HtmxOptions::create()->on('before-request', 'console.log("test")');

        self::assertSame(['on::before-request' => 'console.log("test")'], $options->toArray());
    }

    public function testOnBeforeRequest(): void
    {
        $options = HtmxOptions::create()->onBeforeRequest('console.log("before")');

        self::assertSame(['on::before-request' => 'console.log("before")'], $options->toArray());
    }

    public function testOnAfterRequest(): void
    {
        $options = HtmxOptions::create()->onAfterRequest('console.log("after")');

        self::assertSame(['on::after-request' => 'console.log("after")'], $options->toArray());
    }

    public function testOnConfigRequest(): void
    {
        $options = HtmxOptions::create()->onConfigRequest('event.detail.path = "/modified"');

        self::assertSame(['on::config-request' => 'event.detail.path = "/modified"'], $options->toArray());
    }

    public function testOnBeforeSwap(): void
    {
        $options = HtmxOptions::create()->onBeforeSwap('console.log("swap")');

        self::assertSame(['on::before-swap' => 'console.log("swap")'], $options->toArray());
    }

    public function testOnAfterSwap(): void
    {
        $options = HtmxOptions::create()->onAfterSwap('console.log("swapped")');

        self::assertSame(['on::after-swap' => 'console.log("swapped")'], $options->toArray());
    }

    public function testOnAfterSettle(): void
    {
        $options = HtmxOptions::create()->onAfterSettle('console.log("settled")');

        self::assertSame(['on::after-settle' => 'console.log("settled")'], $options->toArray());
    }

    // ==========================================
    // Miscellaneous
    // ==========================================

    public function testBoost(): void
    {
        $options = HtmxOptions::create()->boost();

        self::assertSame(['boost' => true], $options->toArray());
    }

    public function testBoostFalse(): void
    {
        $options = HtmxOptions::create()->boost(false);

        self::assertSame(['boost' => false], $options->toArray());
    }

    public function testExt(): void
    {
        $options = HtmxOptions::create()->ext('json-enc');

        self::assertSame(['ext' => 'json-enc'], $options->toArray());
    }

    public function testSet(): void
    {
        $options = HtmxOptions::create()->set('custom-attr', 'custom-value');

        self::assertSame(['custom-attr' => 'custom-value'], $options->toArray());
    }

    // ==========================================
    // Fluent Interface
    // ==========================================

    public function testFluentInterface(): void
    {
        $options = HtmxOptions::create()
            ->get('/search')
            ->trigger(Trigger::keyup()->changed()->delay(300))
            ->target('#results')
            ->swap(SwapStyle::InnerHTML)
            ->indicator('#spinner');

        $result = $options->toArray();

        self::assertSame('/search', $result['get']);
        self::assertSame('keyup changed delay:300ms', $result['trigger']);
        self::assertSame('#results', $result['target']);
        self::assertSame('innerHTML', $result['swap']);
        self::assertSame('#spinner', $result['indicator']);
    }

    public function testComplexConfiguration(): void
    {
        $options = HtmxOptions::create()
            ->postRoute('app_validate', ['field' => '{name}'])
            ->trigger(Trigger::blur()->changed()->delay(500))
            ->target('#form_{name}-validation')
            ->swap(SwapStyle::InnerHTML)
            ->include('closest form')
            ->onBeforeRequest('this.classList.add("validating")');

        $result = $options->toArray();

        self::assertInstanceOf(Route::class, $result['post']);
        self::assertSame('blur changed delay:500ms', $result['trigger']);
        self::assertSame('#form_{name}-validation', $result['target']);
        self::assertSame('innerHTML', $result['swap']);
        self::assertSame('closest form', $result['include']);
        self::assertArrayHasKey('on::before-request', $result);
    }
}
