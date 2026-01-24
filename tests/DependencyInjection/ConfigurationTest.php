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
            ],
        ]);

        $this->assertFalse($config['htmx_only']['enabled']);
        $this->assertEquals(403, $config['htmx_only']['status_code']);
        $this->assertEquals('Forbidden', $config['htmx_only']['message']);
        $this->assertFalse($config['default_view_data']['enabled']);
        $this->assertFalse($config['response']['vary_header']);
        $this->assertTrue($config['response']['strict_mode']);
    }
}
