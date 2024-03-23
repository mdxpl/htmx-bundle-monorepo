<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Exception;

use InvalidArgumentException;

class BlockCannotBeSetWithoutTemplate extends InvalidArgumentException implements HtmxBundleException
{
    public static function withBlockName(string $blockName): self
    {
        return new self(
            sprintf('The block "%s" cannot be set without a template.', $blockName),
        );
    }
}
