<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response;

use Symfony\Component\HttpFoundation\Response;

readonly class HtmxResponse
{
    public function __construct(
        public string $template,
        public ?string $blockName,
        public array $viewData,
        public int $responseCode = Response::HTTP_OK,

        /** @var HtmxResponseHeader[] */
        public array $headers = [],
    )
    {
    }
}