<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response;

use Mdxpl\HtmxBundle\Response\Headers\HtmxResponseHeader;
use Symfony\Component\HttpFoundation\Response;

readonly class HtmxResponse
{
    public const RESULT_VIEW_PARAM_NAME = 'htmx_result';
    public const IS_HTMX_REQUEST_VIEW_PARAM_NAME = 'is_htmx_request';

    public function __construct(
        public bool $isFromHtmxRequest,
        public ?string $template,
        public ?string $blockName,
        public array $viewParams,
        public int $responseCode = Response::HTTP_OK,

        /** @var HtmxResponseHeader[] */
        public array $headers = [],
    )
    {
    }
}