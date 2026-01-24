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

        $this->assertTrue($config['htmx_only']['enabled']);
        $this->assertEquals(404, $config['htmx_only']['status_code']);
        $this->assertEquals('Not Found', $config['htmx_only']['message']);
        $this->assertTrue($config['default_view_data']['enabled']);
        $this->assertTrue($config['response']['vary_header']);
        $this->assertFalse($config['response']['strict_mode']);
        $this->assertTrue($config['csrf']['enabled']);
        $this->assertEquals('mdx-htmx', $config['csrf']['token_id']);
        $this->assertEquals('X-CSRF-Token', $config['csrf']['header_name']);
        $this->assertEquals(['GET', 'HEAD', 'OPTIONS'], $config['csrf']['safe_methods']);
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

        $this->assertFalse($config['htmx_only']['enabled']);
        $this->assertEquals(403, $config['htmx_only']['status_code']);
        $this->assertEquals('Forbidden', $config['htmx_only']['message']);
        $this->assertFalse($config['default_view_data']['enabled']);
        $this->assertFalse($config['response']['vary_header']);
        $this->assertTrue($config['response']['strict_mode']);
        $this->assertFalse($config['csrf']['enabled']);
        $this->assertEquals('custom_token', $config['csrf']['token_id']);
        $this->assertEquals('X-Custom-CSRF', $config['csrf']['header_name']);
        $this->assertEquals(['GET'], $config['csrf']['safe_methods']);
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

        $this->assertFalse($config['csrf']['enabled']);
    }
}
