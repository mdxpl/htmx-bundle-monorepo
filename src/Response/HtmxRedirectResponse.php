<?php

declare(strict_types = 1);

namespace Mdxpl\HtmxBundle\Response;

use Mdxpl\HtmxBundle\Response\Headers\Redirect;

readonly class HtmxRedirectResponse extends HtmxResponse
{
    public function __construct(string $url)
    {
        parent::__construct(204, null, new Redirect($url));
    }
}
