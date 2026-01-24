<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response;

use Mdxpl\HtmxBundle\Response\Headers\HtmxResponseHeader;
use Mdxpl\HtmxBundle\Response\Headers\HtmxResponseHeaderCollection;
use Mdxpl\HtmxBundle\Response\View\View;
use Mdxpl\HtmxBundle\Response\View\ViewsCollection;

readonly class HtmxResponse
{
    public ViewsCollection $views;
    public HtmxResponseHeaderCollection $headers;

    public function __construct(
        public int $responseCode = 204,
        ViewsCollection|View|null $view = null,
        HtmxResponseHeaderCollection|HtmxResponseHeader|null $header = null,
    ) {
        $this->views = self::resolveViewsCollection($view);
        $this->headers = self::resolveHeaders($header);
    }

    private static function resolveViewsCollection(View|ViewsCollection|null $view): ViewsCollection
    {
        if ($view instanceof View) {
            return new ViewsCollection($view);
        }

        if ($view instanceof ViewsCollection) {
            return $view;
        }

        return new ViewsCollection();
    }

    private static function resolveHeaders(HtmxResponseHeader|HtmxResponseHeaderCollection|null $header): HtmxResponseHeaderCollection
    {
        if ($header instanceof HtmxResponseHeader) {
            return new HtmxResponseHeaderCollection($header);
        }

        if ($header instanceof HtmxResponseHeaderCollection) {
            return $header;
        }

        return new HtmxResponseHeaderCollection();
    }
}
