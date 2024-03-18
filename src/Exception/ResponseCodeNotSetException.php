<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Exception;

use LogicException;

class ResponseCodeNotSetException extends LogicException
{
    public static function create(): self
    {
        return new self(
            'The response code must be set. Use the withResponseCode(), withSuccess() or withFailure() method.',
        );
    }
}