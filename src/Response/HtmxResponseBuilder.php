<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response;

use LogicException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

class HtmxResponseBuilder
{

    public const DEFAULT_FAILURE_BLOCK_NAME = 'failureComponent';
    public const DEFAULT_SUCCESS_BLOCK_NAME = 'successComponent';
    public const DEFAULT_FORM_BLOCK_NAME = 'formComponent';
    public const DEFAULT_FORM_VIEW_PARAM_NAME = 'form';

    private ?string $blockName = null;

    private array $viewData = [];

    private int $responseCode = Response::HTTP_OK;

    private array $headers = [];

    private function __construct(public readonly bool $fromHtmxRequest, private string $template)
    {
        $this->blockName = $this->fromHtmxRequest ? self::DEFAULT_SUCCESS_BLOCK_NAME : null;
    }

    public static function init(bool $fromHtmxRequest, string $template): self
    {
        return new self($fromHtmxRequest, $template);
    }

    public function withTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function withBadRequest(): self
    {
        $this->responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

        return $this;
    }

    public function withForbiddenRequest(): self
    {
        $this->responseCode = Response::HTTP_FORBIDDEN;

        return $this;
    }

    public function withUnauthorizedRequest(): self
    {
        $this->responseCode = Response::HTTP_UNAUTHORIZED;

        return $this;
    }

    public function withNotFoundRequest(): self
    {
        $this->responseCode = Response::HTTP_NOT_FOUND;

        return $this;
    }

    public function withResponseCode(int $responseCode): self
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    public function withViewData(array $viewData): self
    {
        $this->viewData = $viewData;

        return $this;
    }

    public function withBlock(string $blockName): self
    {
        if(!$this->fromHtmxRequest){
            throw new LogicException('You can only render blocks for htmx requests.');
        }

        $this->blockName = $blockName;

        return $this;
    }

    public function withFailure(string $blockName = self::DEFAULT_FAILURE_BLOCK_NAME): self
    {
        $this->blockName = $this->fromHtmxRequest ? $blockName : null;
        $this->responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

        return $this;
    }

    public function withSuccess(string $blockName = self::DEFAULT_SUCCESS_BLOCK_NAME): self
    {
        $this->blockName = $this->fromHtmxRequest ? $blockName : null;
        $this->responseCode = Response::HTTP_OK;

        return $this;
    }

    public function withHeaders(HtmxResponseHeader ...$headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function withViewParam(string $name, mixed $param): self
    {
        $this->viewData[$name] = $param;

        return $this;
    }

    //todo tests
    public function withForm(
        FormInterface $form,
        $blockName = self::DEFAULT_FORM_BLOCK_NAME,
        $formViewParamName = self::DEFAULT_FORM_VIEW_PARAM_NAME,
    ): self
    {
        $this->blockName = $blockName;
        $this->withViewParam($formViewParamName, $form->createView());

        return $this;
    }

    //todo tests
    public function withFailuredForm(
        FormInterface $form,
        $blockName = self::DEFAULT_FAILURE_BLOCK_NAME,
        $formViewParamName = self::DEFAULT_FORM_VIEW_PARAM_NAME,
    ): self
    {
        $this->blockName = $blockName;
        $this->withViewParam($formViewParamName, $form->createView());

        return $this;
    }

    public function build(): HtmxResponse
    {
        return new HtmxResponse(
            $this->template,
            $this->blockName,
            $this->viewData,
            $this->responseCode,
            $this->headers,
        );
    }
}