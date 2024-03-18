<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response;

use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

//TODO: test each header separately
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

    public function testWithRedirect(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->withRedirect('https://mdx.pl');

        $htmxResponse = $builder->build();

        Assert::assertEquals('HX-Redirect', $htmxResponse->headers['HX-Redirect']->getType()->value);
        Assert::assertEquals('https://mdx.pl', $htmxResponse->headers['HX-Redirect']->getValue());
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

        Assert::assertEquals($htmxResponse->blockName, 'newBlockName');
    }
}
