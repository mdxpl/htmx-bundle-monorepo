<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class HtmxExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('mdx_htmx.htmx_only.enabled', $config['htmx_only']['enabled']);
        $container->setParameter('mdx_htmx.htmx_only.status_code', $config['htmx_only']['status_code']);
        $container->setParameter('mdx_htmx.htmx_only.message', $config['htmx_only']['message']);
        $container->setParameter('mdx_htmx.default_view_data.enabled', $config['default_view_data']['enabled']);
        $container->setParameter('mdx_htmx.response.vary_header', $config['response']['vary_header']);
        $container->setParameter('mdx_htmx.response.strict_mode', $config['response']['strict_mode']);

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.php');
    }

    public function getAlias(): string
    {
        return 'mdx_htmx';
    }
}
