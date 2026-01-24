<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Exception;

use LogicException;

class MissingDependencyException extends LogicException implements HtmxBundleException
{
    public static function csrfPackageRequired(): self
    {
        return new self(
            'CSRF protection requires the "symfony/security-csrf" package. '
            . 'Install it with "composer require symfony/security-csrf" or disable CSRF with "mdx_htmx.csrf.enabled: false".',
        );
    }
}
