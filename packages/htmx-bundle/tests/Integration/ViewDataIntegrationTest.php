<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Integration;

use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for view data handling.
 *
 * Tests common view data, default view data, and view-specific overrides.
 */
class ViewDataIntegrationTest extends TestCase
{
    use TwigIntegrationTestTrait;

    protected function setUp(): void
    {
        $this->setUpTwig();
    }

    /**
     * Tests default view data is passed to all views.
     */
    public function testDefaultViewDataPassedToAllViews(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->viewBlock('withParam.html.twig', 'custom', ['testParam' => 'value1'])
            ->viewBlock('withParam.html.twig', 'body', ['testParam' => 'value2'])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);
        $content = $response->getContent();

        $this->assertStringContainsString('Custom block text value1', $content);
        $this->assertStringContainsString('Body text value2', $content);
    }

    /**
     * Tests common view data is passed to all views.
     */
    public function testCommonViewDataPassedToAllViews(): void
    {
        $commonData = ['testParam' => 'shared-value'];

        $htmxResponse = HtmxResponseBuilder::create(true, $commonData)
            ->success()
            ->viewBlock('withParam.html.twig', 'custom')
            ->viewBlock('withParam.html.twig', 'body')
            ->build();

        $response = $this->responseFactory->create($htmxResponse);
        $content = $response->getContent();

        $this->assertSame(2, substr_count($content, 'shared-value'));
    }

    /**
     * Tests view-specific data overrides common data.
     */
    public function testViewSpecificDataOverridesCommonData(): void
    {
        $commonData = ['testParam' => 'common-value'];

        $htmxResponse = HtmxResponseBuilder::create(true, $commonData)
            ->success()
            ->viewBlock('withParam.html.twig', 'custom', ['testParam' => 'specific-value'])
            ->viewBlock('withParam.html.twig', 'body')
            ->build();

        $response = $this->responseFactory->create($htmxResponse);
        $content = $response->getContent();

        $this->assertStringContainsString('Custom block text specific-value', $content);
        $this->assertStringContainsString('Body text common-value', $content);
    }

    /**
     * Tests that mdx_htmx_result is available in views.
     */
    public function testResultAvailableInViews(): void
    {
        $this->setUpTwig(strictVariables: false);

        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->viewBlock('withDefaultBlocks.html.twig', 'success')
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('Success block', $response->getContent());
    }
}
