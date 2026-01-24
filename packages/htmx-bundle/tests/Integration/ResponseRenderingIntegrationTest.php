<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Integration;

use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for response rendering scenarios from documentation.
 *
 * @see docs/examples/simple_page.md
 * @see docs/examples/form.md
 * @see docs/examples/infinite_scroll.md
 */
class ResponseRenderingIntegrationTest extends TestCase
{
    use TwigIntegrationTestTrait;

    protected function setUp(): void
    {
        $this->setUpTwig();
    }

    /**
     * Tests full page rendering for regular (non-htmx) requests.
     * Based on simple_page.md example.
     */
    public function testSimplePageFullRender(): void
    {
        $viewData = [
            'menu' => [
                'home' => ['name' => 'Home'],
                'about' => ['name' => 'About'],
            ],
            'page' => [
                'name' => 'Home',
                'description' => 'This is the home page',
            ],
        ];

        $htmxResponse = HtmxResponseBuilder::create(false)
            ->success()
            ->view('simple_page.html.twig', $viewData)
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();

        $this->assertStringContainsString('<nav>', $content);
        $this->assertStringContainsString('hx-get="/simple-page/home"', $content);
        $this->assertStringContainsString('hx-get="/simple-page/about"', $content);
        $this->assertStringContainsString('<section id="pageContent">', $content);
        $this->assertStringContainsString('<h1>Home</h1>', $content);
        $this->assertStringContainsString('This is the home page', $content);
    }

    /**
     * Tests partial block rendering for htmx requests.
     * Based on simple_page.md example.
     */
    public function testSimplePagePartialBlockRender(): void
    {
        $viewData = [
            'page' => [
                'name' => 'About',
                'description' => 'This is the about page',
            ],
        ];

        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->viewBlock('simple_page.html.twig', 'pageContentPartial', $viewData)
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();

        $this->assertStringContainsString('<title>About</title>', $content);
        $this->assertStringContainsString('<section id="pageContent">', $content);
        $this->assertStringContainsString('<h1>About</h1>', $content);
        $this->assertStringContainsString('This is the about page', $content);
        $this->assertStringNotContainsString('<nav>', $content);
    }

    /**
     * Tests form success response rendering.
     * Based on form.md example.
     */
    public function testFormSuccessResponse(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->viewBlock('form.html.twig', 'successComponent')
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('Great success!', $response->getContent());
        $this->assertStringNotContainsString('<form', $response->getContent());
    }

    /**
     * Tests form failure response rendering with 422 status code.
     * Based on form.md example.
     */
    public function testFormFailureResponse(): void
    {
        $viewData = [
            'formData' => ['name' => 'A'],
            'errors' => ['Name must be at least 2 characters'],
        ];

        $htmxResponse = HtmxResponseBuilder::create(true)
            ->failure()
            ->viewBlock('form.html.twig', 'failureComponent', $viewData)
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $this->assertSame(422, $response->getStatusCode());
        $content = $response->getContent();

        $this->assertStringContainsString('Fix the errors in the form!', $content);
        $this->assertStringContainsString('<form', $content);
        $this->assertStringContainsString('Name must be at least 2 characters', $content);
        $this->assertStringContainsString('value="A"', $content);
    }

    /**
     * Tests form initial page load with full page.
     * Based on form.md example.
     */
    public function testFormInitialPageLoad(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(false)
            ->success()
            ->view('form.html.twig', ['formData' => [], 'errors' => []])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();

        $this->assertStringContainsString('<div id="formWrapper">', $content);
        $this->assertStringContainsString('<form', $content);
        $this->assertStringContainsString('hx-post="/demo"', $content);
        $this->assertStringContainsString('hx-target="#formWrapper"', $content);
    }

    /**
     * Tests form block rendering for htmx initial load.
     * Based on form.md example.
     */
    public function testFormHtmxInitialLoad(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->viewBlock('form.html.twig', 'formComponent', ['formData' => [], 'errors' => []])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();

        $this->assertStringContainsString('<form', $content);
        $this->assertStringNotContainsString('<div id="formWrapper">', $content);
    }

    /**
     * Tests infinite scroll first page rendering.
     * Based on infinite_scroll.md example.
     */
    public function testInfiniteScrollFirstPage(): void
    {
        $viewData = [
            'items' => ['Item 1', 'Item 2', 'Item 3'],
            'nextPageNumber' => 2,
        ];

        $htmxResponse = HtmxResponseBuilder::create(false)
            ->success()
            ->view('infinite_scroll.html.twig', $viewData)
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();

        $this->assertStringContainsString('<h1>An infinite scroll example</h1>', $content);
        $this->assertStringContainsString('<div id="items"', $content);
        $this->assertStringContainsString('Item 1', $content);
        $this->assertStringContainsString('Item 2', $content);
        $this->assertStringContainsString('Item 3', $content);
        $this->assertStringContainsString('hx-get="/infinite-scroll?pageNumber=2"', $content);
        $this->assertStringContainsString('hx-trigger="revealed"', $content);
    }

    /**
     * Tests infinite scroll subsequent page rendering (htmx request).
     * Based on infinite_scroll.md example.
     */
    public function testInfiniteScrollSubsequentPage(): void
    {
        $viewData = [
            'items' => ['Item 4', 'Item 5', 'Item 6'],
            'nextPageNumber' => 3,
        ];

        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->viewBlock('infinite_scroll.html.twig', 'items', $viewData)
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $this->assertSame(200, $response->getStatusCode());
        $content = $response->getContent();

        $this->assertStringContainsString('Item 4', $content);
        $this->assertStringContainsString('Item 5', $content);
        $this->assertStringContainsString('Item 6', $content);
        $this->assertStringContainsString('hx-get="/infinite-scroll?pageNumber=3"', $content);
        $this->assertStringNotContainsString('<h1>An infinite scroll example</h1>', $content);
        $this->assertStringNotContainsString('<div id="items"', $content);
    }

    /**
     * Tests that only the last item in infinite scroll has hx-get attribute.
     */
    public function testInfiniteScrollOnlyLastItemHasHxGet(): void
    {
        $viewData = [
            'items' => ['Item 1', 'Item 2', 'Item 3'],
            'nextPageNumber' => 2,
        ];

        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->viewBlock('infinite_scroll.html.twig', 'items', $viewData)
            ->build();

        $response = $this->responseFactory->create($htmxResponse);
        $content = $response->getContent();

        $this->assertSame(1, substr_count($content, 'hx-get='));
        $this->assertSame(1, substr_count($content, 'hx-trigger="revealed"'));
    }

    /**
     * Tests response Vary header is set correctly.
     */
    public function testVaryHeaderIsSet(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->viewBlock('simple_page.html.twig', 'pageContentPartial', [
                'page' => ['name' => 'Test', 'description' => 'Test'],
            ])
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $this->assertSame('HX-Request', $response->headers->get('Vary'));
    }

    /**
     * Tests Vary header can be disabled.
     */
    public function testVaryHeaderCanBeDisabled(): void
    {
        $responseFactory = $this->createResponseFactory(addVaryHeader: false);

        $htmxResponse = HtmxResponseBuilder::create(true)
            ->success()
            ->viewBlock('simple_page.html.twig', 'pageContentPartial', [
                'page' => ['name' => 'Test', 'description' => 'Test'],
            ])
            ->build();

        $response = $responseFactory->create($htmxResponse);

        $this->assertNull($response->headers->get('Vary'));
    }

    /**
     * Tests empty response (no content, 204 status).
     */
    public function testNoContentResponse(): void
    {
        $htmxResponse = HtmxResponseBuilder::create(true)
            ->noContent()
            ->build();

        $response = $this->responseFactory->create($htmxResponse);

        $this->assertSame(204, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }
}
