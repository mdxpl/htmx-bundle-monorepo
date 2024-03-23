<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response;

use Mdxpl\HtmxBundle\Response\Headers\Refresh;

readonly class HtmxRefreshResponse extends HtmxResponse
{
    public function __construct()
    {
        parent::__construct(204, null, new Refresh());
    }
}
