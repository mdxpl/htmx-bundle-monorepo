<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class ResponseFactory
{
    public function __construct(private readonly Environment $twig)
    {
    }

    /**
     * Creates a Symfony http, html response
     */
    public function create(HtmxResponse $htmxResponse): Response
    {
        $response = new Response(null, $htmxResponse->responseCode);
        $this->setHeaders($htmxResponse, $response);

        // Return empty response if no template is set
        if ($htmxResponse->template === null) {
            return $response;
        }

        // Render block if from htmx request and block name is set
        if ($htmxResponse->isFromHtmxRequest && $htmxResponse->blockName !== null) {
            return $response->setContent(
                $this->twig->load($htmxResponse->template)->renderBlock(
                    $htmxResponse->blockName,
                    $htmxResponse->viewParams,
                )
            );
        }

        // Otherwise render the whole template
        return $response->setContent(
            $this->twig->load($htmxResponse->template)->render(
                $htmxResponse->viewParams,
            )
        );
    }

    private function setHeaders(HtmxResponse $htmxResponse, Response $response): void
    {
        foreach ($htmxResponse->headers as $header) {
            $response->headers->set($header->getType()->value, $header->getValue());
        }
    }
}