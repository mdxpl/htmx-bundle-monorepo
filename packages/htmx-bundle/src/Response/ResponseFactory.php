<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response;

use Mdxpl\HtmxBundle\Response\View\View;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ResponseFactory
{
    public function __construct(
        private readonly Environment $twig,
        private readonly bool $addVaryHeader = true,
    ) {
    }

    /**
     * Creates a Symfony HTTP response
     */
    public function create(HtmxResponse $htmxResponse): Response
    {
        $response = new Response(null, $htmxResponse->responseCode);
        $this->setHeaders($htmxResponse, $response);

        if ($this->addVaryHeader) {
            $response->headers->set('Vary', 'HX-Request', false);
        }

        if ($htmxResponse->views->isEmpty()) {
            return $response;
        }

        $renderedViews = $htmxResponse->views->map(fn (View $view) => $this->renderView($view));
        $combinedViews = implode($this->getTemplatesSeparator(), $renderedViews);

        return $response->setContent($combinedViews);
    }

    private function renderView(View $view): string
    {
        if ($view->template === null) {
            return '';
        }

        $templateWrapper = $this->twig->load($view->template);

        return $view->block !== null
            ? $templateWrapper->renderBlock($view->block, $view->data)
            : $templateWrapper->render($view->data);
    }

    private function setHeaders(HtmxResponse $htmxResponse, Response $response): void
    {
        foreach ($htmxResponse->headers as $header) {
            $response->headers->set($header->getType()->value, $header->getValue());
        }
    }

    private function getTemplatesSeparator(): string
    {
        return str_repeat(PHP_EOL, 2);
    }
}
