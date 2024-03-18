<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Controller;

use LogicException;
use Mdxpl\HtmxBundle\Controller\HtmxTrait;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class HtmxTraitTest extends KernelTestCase
{

    public function testTraitCannotBeUsedOutsideAbstractController(): void
    {
        $this->expectException(LogicException::class);

        $controller = new class() {
            use HtmxTrait;

            public function test(): Response
            {
                $builder = HtmxResponseBuilder::init(false, 'withCustomBlock.html.twig');
                return $this->renderHtmx($builder);
            }
        };

        $controller->test();
    }

    /**
     * @dataProvider provideScenarios
     */
    public function tests(
        bool $fromHtmxRequest,
        string $case,
        string $expectedContent,
        int $expectedResponseCode,
        bool $expectException,
    ): void
    {
        if ($expectException) {
            $this->expectException(LogicException::class);
        }

        $controller = new class($fromHtmxRequest, $case, $expectedContent, $expectedResponseCode) extends AbstractController {
            use HtmxTrait;

            public function __construct(
                public bool $fromHtmxRequest,
                public string $case,
                public string $expectedContent,
                public int $expectedResponseCode,
            )
            {
            }

            public function test(): Response
            {
                $builder = HtmxResponseBuilder::init(
                    $this->fromHtmxRequest,
                    'withDefaultBlocks.html.twig',
                );

                return match ($this->case) {
                    'success' => $this->renderHtmxSuccess($builder),
                    'failure' => $this->renderHtmxFailure($builder),
                    'custom' => $this->renderHtmx($builder->withBlock('custom')),
                };
            }
        };
        $controller->setContainer($this->initContainer());

        $response = $controller->test();

        $this->assertEquals($expectedContent, $response->getContent());
        $this->assertEquals($expectedResponseCode, $response->getStatusCode());
    }

    public function provideScenarios(): array
    {
        return [
            'Htmx request renders with Default Blocks' => [true, 'success', 'Inside the default success block', 200, false],

            'Render htmx success' => [true, 'success', 'Inside the default success block', 200, false],
            'Non-htmx render htmx success' => [false, 'success', 'Outside of the default blocks', 200, false],

            'Render htmx failure' => [true, 'failure', 'Inside the default failure block', 422, false],
            'Non-htmx render htmx failure' => [false, 'failure', 'Outside of the default blocks', 422, false],

            'Htmx request renders with Custom Block' => [true, 'custom', 'Inside the custom block', 200, false],
            'Non-htmx request Renders With Custom Block' => [false, 'custom', 'Outside of the custom', 200, true],
        ];
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

    public function initContainer(): ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn($this->initTwig());

        return $container;
    }
}
