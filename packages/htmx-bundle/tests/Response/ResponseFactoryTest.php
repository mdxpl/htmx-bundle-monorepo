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
        self::assertEquals(204, $response->getStatusCode());
    }

    public function testCreateWithTemplateHtmxWithBlockRendersSelectedBlock(): void
    {
        $builder = HtmxResponseBuilder::create(true)->success()
            ->viewBlock('withDefaultBlocks.html.twig', 'custom');

        $factory = new ResponseFactory($this->initTwig());
        $response = $factory->create($builder->build());

        self::assertEquals('Custom block text', $response->getContent());
        self::assertEquals(200, $response->getStatusCode());
    }

    public function testCreateWithFailureReturnsErrorCode(): void
    {
        $builder = HtmxResponseBuilder::create(true)->failure();
        $factory = new ResponseFactory($this->initTwig());
        $response = $factory->create($builder->build());

        self::assertEquals(422, $response->getStatusCode());
    }

    public function testCreateWithHeaderSetsHeader(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->redirect('https://mdx.pl');
        $factory = new ResponseFactory($this->initTwig());
        $response = $factory->create($builder->build());

        self::assertTrue($response->headers->has('HX-Redirect'));
        self::assertEquals('https://mdx.pl', $response->headers->get('HX-Redirect'));
    }

    public function testCreateWithViewParamAddsNewParamToFullPage(): void
    {
        $builder = HtmxResponseBuilder::create(false)
            ->view('withParam.html.twig', ['testParam' => 'MDX']);

        $factory = new ResponseFactory($this->initTwig());
        $response = $factory->create($builder->build());

        self::assertEquals('Body text MDX', $response->getContent());
    }

    public function testCreateWithViewParamAddsNewParamToBlock(): void
    {
        $builder = HtmxResponseBuilder::create(true)
            ->viewBlock('withParam.html.twig', 'custom', ['testParam' => 'MDX']);

        $factory = new ResponseFactory($this->initTwig());
        $response = $factory->create($builder->build());

        self::assertEquals('Custom block text MDX', $response->getContent());
    }

    public function testNoContentViewReturnsEmptyString(): void
    {
        $builder = HtmxResponseBuilder::create(true)->view('');

        $factory = new ResponseFactory($this->initTwig());
        $response = $factory->create($builder->build());

        self::assertSame('', $response->getContent());
    }

    public function testVaryHeaderIsAddedByDefault(): void
    {
        $builder = HtmxResponseBuilder::create(true)->success();
        $factory = new ResponseFactory($this->initTwig());
        $response = $factory->create($builder->build());

        self::assertTrue($response->headers->has('Vary'));
        self::assertEquals('HX-Request', $response->headers->get('Vary'));
    }

    public function testVaryHeaderCanBeDisabled(): void
    {
        $builder = HtmxResponseBuilder::create(true)->success();
        $factory = new ResponseFactory($this->initTwig(), addVaryHeader: false);
        $response = $factory->create($builder->build());

        self::assertFalse($response->headers->has('Vary'));
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
