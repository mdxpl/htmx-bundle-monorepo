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

        self::assertSame(200, $response->getStatusCode());
        $content = $response->getContent();

        self::assertStringContainsString('<nav>', $content);
        self::assertStringContainsString('hx-get="/simple-page/home"', $content);
        self::assertStringContainsString('hx-get="/simple-page/about"', $content);
        self::assertStringContainsString('<section id="pageContent">', $content);
        self::assertStringContainsString('<h1>Home</h1>', $content);
        self::assertStringContainsString('This is the home page', $content);
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

        self::assertSame(200, $response->getStatusCode());
        $content = $response->getContent();

        self::assertStringContainsString('<title>About</title>', $content);
        self::assertStringContainsString('<section id="pageContent">', $content);
        self::assertStringContainsString('<h1>About</h1>', $content);
        self::assertStringContainsString('This is the about page', $content);
        self::assertStringNotContainsString('<nav>', $content);
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

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Great success!', $response->getContent());
        self::assertStringNotContainsString('<form', $response->getContent());
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

        self::assertSame(422, $response->getStatusCode());
        $content = $response->getContent();

        self::assertStringContainsString('Fix the errors in the form!', $content);
        self::assertStringContainsString('<form', $content);
        self::assertStringContainsString('Name must be at least 2 characters', $content);
        self::assertStringContainsString('value="A"', $content);
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

        self::assertSame(200, $response->getStatusCode());
        $content = $response->getContent();

        self::assertStringContainsString('<div id="formWrapper">', $content);
        self::assertStringContainsString('<form', $content);
        self::assertStringContainsString('hx-post="/demo"', $content);
        self::assertStringContainsString('hx-target="#formWrapper"', $content);
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

        self::assertSame(200, $response->getStatusCode());
        $content = $response->getContent();

        self::assertStringContainsString('<form', $content);
        self::assertStringNotContainsString('<div id="formWrapper">', $content);
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

        self::assertSame(200, $response->getStatusCode());
        $content = $response->getContent();

        self::assertStringContainsString('<h1>An infinite scroll example</h1>', $content);
        self::assertStringContainsString('<div id="items"', $content);
        self::assertStringContainsString('Item 1', $content);
        self::assertStringContainsString('Item 2', $content);
        self::assertStringContainsString('Item 3', $content);
        self::assertStringContainsString('hx-get="/infinite-scroll?pageNumber=2"', $content);
        self::assertStringContainsString('hx-trigger="revealed"', $content);
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

        self::assertSame(200, $response->getStatusCode());
        $content = $response->getContent();

        self::assertStringContainsString('Item 4', $content);
        self::assertStringContainsString('Item 5', $content);
        self::assertStringContainsString('Item 6', $content);
        self::assertStringContainsString('hx-get="/infinite-scroll?pageNumber=3"', $content);
        self::assertStringNotContainsString('<h1>An infinite scroll example</h1>', $content);
        self::assertStringNotContainsString('<div id="items"', $content);
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

        self::assertSame(1, substr_count($content, 'hx-get='));
        self::assertSame(1, substr_count($content, 'hx-trigger="revealed"'));
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

        self::assertSame('HX-Request', $response->headers->get('Vary'));
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

        self::assertNull($response->headers->get('Vary'));
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

        self::assertSame(204, $response->getStatusCode());
        self::assertEmpty($response->getContent());
    }
}
