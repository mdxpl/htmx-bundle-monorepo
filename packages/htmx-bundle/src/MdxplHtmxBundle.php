<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle;

use Mdxpl\HtmxBundle\DependencyInjection\HtmxExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MdxplHtmxBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        if ($this->extension === false) {
            return null;
        }

        return $this->extension ??= new HtmxExtension();
    }
}
