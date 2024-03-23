<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response;

enum Result: string
{
    case SUCCESS = 'SUCCESS';
    case FAILURE = 'FAILURE';
    case UNKNOWN = 'UNKNOWN';

    public function isSuccess(): bool
    {
        return $this === self::SUCCESS;
    }

    public function isFailure(): bool
    {
        return $this === self::FAILURE;
    }
}
