<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Exception;

use LogicException;

class ReservedViewParamCannotBeOverriddenException extends LogicException
{
    public static function withViewParamName(string $viewParamName): self
    {
        return new self(
            sprintf('The view param "%s" is reserved and cannot be overridden.', $viewParamName),
        );
    }
}