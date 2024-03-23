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
    )
    {
        $this->resolveHeaders($header);
        $this->resolveViewsCollection($view);
    }

    private function resolveViewsCollection(View|ViewsCollection|null $view): void
    {
        if ($view instanceof View) {
            $this->views = new ViewsCollection($view);

            return;
        }

        if ($view instanceof ViewsCollection) {
            $this->views = $view;

            return;
        }

        $this->views = new ViewsCollection();
    }

    private function resolveHeaders(HtmxResponseHeader|HtmxResponseHeaderCollection|null $header): void
    {
        if ($header instanceof HtmxResponseHeader) {
            $this->headers = new HtmxResponseHeaderCollection($header);

            return;
        }

        if ($header instanceof HtmxResponseHeaderCollection) {
            $this->headers = $header;

            return;
        }

        $this->headers = new HtmxResponseHeaderCollection();
    }
}
