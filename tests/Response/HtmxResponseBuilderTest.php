<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response;

use LogicException;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Mdxpl\HtmxBundle\Response\HtmxResponseHeader;
use Mdxpl\HtmxBundle\Response\HtmxResponseHeaderType;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class HtmxResponseBuilderTest extends TestCase
{
    public function testInitForNonHtmxRequest(): void
    {
        $builder = HtmxResponseBuilder::init(false, 'template.html.twig');

        $htmxResponse = $builder->build();

        Assert::assertEquals('template.html.twig', $htmxResponse->template);

        Assert::assertNull($htmxResponse->blockName);

        Assert::assertIsArray($htmxResponse->viewData);
        Assert::assertEmpty($htmxResponse->viewData);

        Assert::assertEquals(200, $htmxResponse->responseCode);

        Assert::assertIsArray($htmxResponse->headers);
        Assert::assertEmpty($htmxResponse->headers);
    }

    public function testInitForHtmxRequest(): void
    {
        $builder = HtmxResponseBuilder::init(true, 'template.html.twig');

        $htmxResponse = $builder->build();

        Assert::assertEquals('template.html.twig', $htmxResponse->template);

        Assert::assertEquals('successComponent', $htmxResponse->blockName);

        Assert::assertIsArray($htmxResponse->viewData);
        Assert::assertEmpty($htmxResponse->viewData);

        Assert::assertEquals(200, $htmxResponse->responseCode);

        Assert::assertIsArray($htmxResponse->headers);
        Assert::assertEmpty($htmxResponse->headers);
    }

    public function testWithHeaders(): void
    {
        $builder = HtmxResponseBuilder::init(true, 'template.html.twig')
            ->withHeaders(new HtmxResponseHeader(HtmxResponseHeaderType::REDIRECT, 'https://mdx.pl'));

        $htmxResponse = $builder->build();

        Assert::assertIsArray($htmxResponse->headers);
        Assert::assertCount(1, $htmxResponse->headers);
        Assert::assertEquals('HX-Redirect', $htmxResponse->headers[0]->type->value);
        Assert::assertEquals('https://mdx.pl', $htmxResponse->headers[0]->value);
    }

    public function testWithViewParamAddsNewParam(): void
    {
        $builder = HtmxResponseBuilder::init(true, 'template.html.twig')
            ->withViewParam('param1', 'value1');

        $htmxResponse = $builder->build();

        Assert::assertIsArray($htmxResponse->viewData);
        Assert::assertCount(1, $htmxResponse->viewData);
        Assert::assertArrayHasKey('param1', $htmxResponse->viewData);
        Assert::assertContains('value1', $htmxResponse->viewData);
    }

    public function testWithViewDataReplacesAllParams(): void
    {
        $builder = HtmxResponseBuilder::init(true, 'template.html.twig')
            ->withViewParam('param1', 'value1')
            ->withViewData(['param2' => 'value2']);

        $htmxResponse = $builder->build();

        Assert::assertIsArray($htmxResponse->viewData);
        Assert::assertCount(1, $htmxResponse->viewData);
        Assert::assertArrayNotHasKey('param1', $htmxResponse->viewData);
        Assert::assertArrayHasKey('param2', $htmxResponse->viewData);
        Assert::assertContains('value2', $htmxResponse->viewData);
    }

    public function testWithTemplateReplacesTemplate(): void
    {
        $builder = HtmxResponseBuilder::init(true, 'template.html.twig')
            ->withTemplate('new-template.html.twig');

        $htmxResponse = $builder->build();

        Assert::assertEquals($htmxResponse->template, 'new-template.html.twig');
    }

    public function testWithBlockSetsBlockName(): void
    {
        $builder = HtmxResponseBuilder::init(true, 'template.html.twig')
            ->withBlock('newBlockName');

        $htmxResponse = $builder->build();

        Assert::assertEquals($htmxResponse->blockName, 'newBlockName');
    }

    public function testBlockCannotBeRenderedForNonHtmxRequest(): void
    {
        $this->expectException(LogicException::class);

        HtmxResponseBuilder::init(false, 'template.html.twig')
            ->withBlock('newBlockName');
    }
}
