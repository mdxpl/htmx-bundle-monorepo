<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\DependencyInjection;

use Mdxpl\HtmxBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testDefaultConfiguration(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $config = $processor->processConfiguration($configuration, []);

        self::assertTrue($config['htmx_only']['enabled']);
        self::assertEquals(404, $config['htmx_only']['status_code']);
        self::assertEquals('Not Found', $config['htmx_only']['message']);
        self::assertTrue($config['default_view_data']['enabled']);
        self::assertTrue($config['response']['vary_header']);
        self::assertFalse($config['response']['strict_mode']);
        self::assertTrue($config['csrf']['enabled']);
        self::assertEquals('mdx-htmx', $config['csrf']['token_id']);
        self::assertEquals('X-CSRF-Token', $config['csrf']['header_name']);
        self::assertEquals(['GET', 'HEAD', 'OPTIONS'], $config['csrf']['safe_methods']);
    }

    public function testCustomConfiguration(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $config = $processor->processConfiguration($configuration, [
            'mdx_htmx' => [
                'htmx_only' => [
                    'enabled' => false,
                    'status_code' => 403,
                    'message' => 'Forbidden',
                ],
                'default_view_data' => [
                    'enabled' => false,
                ],
                'response' => [
                    'vary_header' => false,
                    'strict_mode' => true,
                ],
                'csrf' => [
                    'enabled' => false,
                    'token_id' => 'custom_token',
                    'header_name' => 'X-Custom-CSRF',
                    'safe_methods' => ['GET'],
                ],
            ],
        ]);

        self::assertFalse($config['htmx_only']['enabled']);
        self::assertEquals(403, $config['htmx_only']['status_code']);
        self::assertEquals('Forbidden', $config['htmx_only']['message']);
        self::assertFalse($config['default_view_data']['enabled']);
        self::assertFalse($config['response']['vary_header']);
        self::assertTrue($config['response']['strict_mode']);
        self::assertFalse($config['csrf']['enabled']);
        self::assertEquals('custom_token', $config['csrf']['token_id']);
        self::assertEquals('X-Custom-CSRF', $config['csrf']['header_name']);
        self::assertEquals(['GET'], $config['csrf']['safe_methods']);
    }

    public function testCsrfCanBeDisabled(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $config = $processor->processConfiguration($configuration, [
            'mdx_htmx' => [
                'csrf' => false,
            ],
        ]);

        self::assertFalse($config['csrf']['enabled']);
    }
}
