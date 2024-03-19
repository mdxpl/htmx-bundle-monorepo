<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Response;

use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Mdxpl\HtmxBundle\Response\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ResponseFactoryTest extends TestCase
{
    public function testCreateWithoutTemplateRendersEmptyResponse(): void
    {
        $builder = HtmxResponseBuilder::create(false);
        $factory = new ResponseFactory($this->initTwig());
        $response = $factory->create($builder->build());

        self::assertEmpty($response->getContent());
        self::assertEquals(200, $response->getStatusCode());
    }

    public function testCreateWithTemplateNonHtmxRendersFullPage(): void
    {
        $builder = HtmxResponseBuilder::create(false)
            ->withTemplate('withDefaultBlocks.html.twig');
        $factory = new ResponseFactory($this->initTwig());
        $response = $factory->create($builder->build());

        self::assertEquals('Body text', $response->getContent());
        self::assertEquals(200, $response->getStatusCode());
    }

    public function testCreateWithTemplateHtmxRendersFullPage(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->withTemplate('withDefaultBlocks.html.twig');
        $factory = new ResponseFactory($this->initTwig());
        $response = $factory->create($builder->build());

        self::assertEquals('Body text', $response->getContent());
        self::assertEquals(200, $response->getStatusCode());
    }

    public function testCreateWithTemplateNonHtmxWithBlockRendersFullPage(): void
    {
        $builder = HtmxResponseBuilder::create(false)
            ->withTemplate('withDefaultBlocks.html.twig')
            ->withBlock('custom');

        $factory = new ResponseFactory($this->initTwig());
        $response = $factory->create($builder->build());

        self::assertEquals('Body text', $response->getContent());
        self::assertEquals(200, $response->getStatusCode());
    }

    public function testCreateWithTemplateHtmxWithBlockRendersSelectedBlock(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->withTemplate('withDefaultBlocks.html.twig')
            ->withBlock('custom');
        $factory = new ResponseFactory($this->initTwig());
        $response = $factory->create($builder->build());

        self::assertEquals('Custom block text', $response->getContent());
        self::assertEquals(200, $response->getStatusCode());
    }

    public function testCreateWithFailureReturnsErrorCode(): void
    {
        $builder = HtmxResponseBuilder::create(true)->withFailure();
        $factory = new ResponseFactory($this->initTwig());
        $response = $factory->create($builder->build());

        self::assertEquals(422, $response->getStatusCode());
    }

    public function testCreateWithHeaderSetsHeader(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->withRedirect('https://mdx.pl');
        $factory = new ResponseFactory($this->initTwig());
        $response = $factory->create($builder->build());

        self::assertTrue($response->headers->has('HX-Redirect'));
        self::assertEquals('https://mdx.pl', $response->headers->get('HX-Redirect'));
    }

    public function testCreateWithViewParamAddsNewParamToFullPage(): void
    {
        $builder = HtmxResponseBuilder::create(false)
            ->withTemplate('withParam.html.twig')
            ->withViewParam('testParam', 'MDX');
        $factory = new ResponseFactory($this->initTwig());
        $response = $factory->create($builder->build());

        self::assertEquals('Body text MDX', $response->getContent());
    }

    public function testCreateWithViewParamAddsNewParamToBlock(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->withTemplate('withParam.html.twig')
            ->withBlock('custom')
            ->withViewParam('testParam', 'MDX');
        $factory = new ResponseFactory($this->initTwig());
        $response = $factory->create($builder->build());

        self::assertEquals('Custom block text MDX', $response->getContent());
    }

    /**
     * @return Environment
     */
    public function initTwig(): Environment
    {
        $loader = new FilesystemLoader([
            __DIR__ . '/../Templates',
        ]);

        return new Environment($loader);
    }
}
