<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\DependencyInjection;

use Mdxpl\HtmxBundle\EventSubscriber\CsrfValidationSubscriber;
use Mdxpl\HtmxBundle\Exception\MissingDependencyException;
use Mdxpl\HtmxBundle\Twig\HtmxCsrfExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

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

        $this->configureCsrf($config['csrf'], $container);
    }

    /**
     * @param array{enabled: bool, token_id: string, header_name: string, safe_methods: list<string>} $config
     */
    private function configureCsrf(array $config, ContainerBuilder $container): void
    {
        if (!$config['enabled']) {
            return;
        }

        if (!interface_exists(CsrfTokenManagerInterface::class)) {
            throw MissingDependencyException::csrfPackageRequired();
        }

        $container->register(HtmxCsrfExtension::class)
            ->setArguments([
                new Reference('security.csrf.token_manager'),
                $config['token_id'],
                $config['header_name'],
            ])
            ->addTag('twig.extension');

        $container->register(CsrfValidationSubscriber::class)
            ->setArguments([
                new Reference('security.csrf.token_manager'),
                true,
                $config['token_id'],
                $config['header_name'],
                $config['safe_methods'],
            ])
            ->addTag('kernel.event_subscriber');
    }

    public function getAlias(): string
    {
        return 'mdx_htmx';
    }
}
