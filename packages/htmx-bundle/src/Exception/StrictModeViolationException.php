<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Exception;

use LogicException;

class StrictModeViolationException extends LogicException implements HtmxBundleException
{
    public static function htmxResponseForNonHtmxRequest(): self
    {
        return new self(
            'HtmxResponse returned for non-htmx request. '
            . 'This is likely a bug. Disable strict_mode in mdx_htmx config to suppress this error.',
        );
    }
}
