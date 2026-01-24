<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Integration;

use Mdxpl\HtmxBundle\Response\ResponseFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

trait TwigIntegrationTestTrait
{
    protected Environment $twig;
    protected ResponseFactory $responseFactory;

    protected function setUpTwig(bool $strictVariables = true): void
    {
        $loader = new FilesystemLoader([__DIR__ . '/../Templates']);
        $this->twig = new Environment($loader, ['strict_variables' => $strictVariables]);
        $this->responseFactory = new ResponseFactory($this->twig);
    }

    protected function createResponseFactory(bool $addVaryHeader = true): ResponseFactory
    {
        return new ResponseFactory($this->twig, $addVaryHeader);
    }
}
