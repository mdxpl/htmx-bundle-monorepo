<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle;

use Mdxpl\HtmxBundle\DependencyInjection\HtmxExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MdxplHtmxBundle extends Bundle
{
    protected function createContainerExtension(): ?ExtensionInterface
    {
        return new HtmxExtension();
    }
}
