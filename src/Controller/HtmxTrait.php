<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Controller;

use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

trait HtmxTrait
{
    /**
     * @param string[] $extraHeaders key => value array of extra headers other than HTMX response headers
     */
    protected function renderHtmx(HtmxResponseBuilder $htmxResponseBuilder, array $extraHeaders = []): Response
    {
        $htmxResponse = $htmxResponseBuilder->build();

        $response = new Response(null, $htmxResponse->responseCode);
        foreach($htmxResponse->headers as $header){
            $response->headers->set($header->type->value, $header->value);
        }
        $response->headers->add($extraHeaders);

        $this->assertExtendsSymfonyAbstractController();

        if ($htmxResponseBuilder->fromHtmxRequest) {
            return $this->renderBlock(
                $htmxResponse->template,
                $htmxResponse->blockName,
                $htmxResponse->viewData,
                $response,
            );
        }

        return $this->render($htmxResponse->template, $htmxResponse->viewData, $response);
    }

    protected function renderHtmxSuccess(HtmxResponseBuilder $htmxResponseBuilder, array $extraHeaders = []): Response
    {
        $htmxResponseBuilder->withSuccess();

        return $this->renderHtmx($htmxResponseBuilder, $extraHeaders);
    }

    protected function renderHtmxFailure(HtmxResponseBuilder $htmxResponseBuilder, array $extraHeaders = []): Response
    {
        $htmxResponseBuilder->withBadRequest();

        return $this->renderHtmx($htmxResponseBuilder, $extraHeaders);
    }

    private function assertExtendsSymfonyAbstractController(): void
    {
        if (!$this instanceof AbstractController) {
            throw new LogicException(
                sprintF(
                    'The %s can only be used in a class that extends %s.',
                    HtmxTrait::class,
                    AbstractController::class,
                ),
            );
        }
    }
}