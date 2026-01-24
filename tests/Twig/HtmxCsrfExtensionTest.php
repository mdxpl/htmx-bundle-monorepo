<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Twig;

use Mdxpl\HtmxBundle\Twig\HtmxCsrfExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class HtmxCsrfExtensionTest extends TestCase
{
    public function testGetFunctionsReturnsExpectedFunctions(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $extension = new HtmxCsrfExtension($tokenManager);

        $functions = $extension->getFunctions();

        $this->assertCount(3, $functions);

        $functionNames = array_map(fn ($f) => $f->getName(), $functions);
        $this->assertContains('htmx_csrf_token', $functionNames);
        $this->assertContains('htmx_csrf_meta', $functionNames);
        $this->assertContains('htmx_csrf_headers', $functionNames);
    }

    public function testGetCsrfTokenReturnsTokenValue(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->expects($this->once())
            ->method('getToken')
            ->with('mdx-htmx')
            ->willReturn(new CsrfToken('mdx-htmx', 'test-token-value'));

        $extension = new HtmxCsrfExtension($tokenManager);

        $this->assertSame('test-token-value', $extension->getCsrfToken());
    }

    public function testGetCsrfMetaReturnsMetaTag(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->expects($this->once())
            ->method('getToken')
            ->with('mdx-htmx')
            ->willReturn(new CsrfToken('mdx-htmx', 'meta-token-value'));

        $extension = new HtmxCsrfExtension($tokenManager);

        $this->assertSame(
            '<meta name="csrf-token" content="meta-token-value">',
            $extension->getCsrfMeta(),
        );
    }

    public function testGetCsrfMetaEscapesHtmlCharacters(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->expects($this->once())
            ->method('getToken')
            ->with('mdx-htmx')
            ->willReturn(new CsrfToken('mdx-htmx', 'token"with<special>chars'));

        $extension = new HtmxCsrfExtension($tokenManager);

        $this->assertSame(
            '<meta name="csrf-token" content="token&quot;with&lt;special&gt;chars">',
            $extension->getCsrfMeta(),
        );
    }

    public function testGetCsrfHeadersReturnsHxHeadersAttribute(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->expects($this->once())
            ->method('getToken')
            ->with('mdx-htmx')
            ->willReturn(new CsrfToken('mdx-htmx', 'header-token-value'));

        $extension = new HtmxCsrfExtension($tokenManager);

        $this->assertSame(
            'hx-headers=\'{"X-CSRF-Token":"header-token-value"}\'',
            $extension->getCsrfHeaders(),
        );
    }

    public function testGetCsrfHeadersWithCustomHeaderName(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->expects($this->once())
            ->method('getToken')
            ->with('mdx-htmx')
            ->willReturn(new CsrfToken('mdx-htmx', 'custom-header-token'));

        $extension = new HtmxCsrfExtension($tokenManager, 'mdx-htmx', 'X-Custom-Token');

        $this->assertSame(
            'hx-headers=\'{"X-Custom-Token":"custom-header-token"}\'',
            $extension->getCsrfHeaders(),
        );
    }

    public function testGetCsrfHeadersEscapesSpecialCharacters(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->expects($this->once())
            ->method('getToken')
            ->with('mdx-htmx')
            ->willReturn(new CsrfToken('mdx-htmx', "token'with\"quotes"));

        $extension = new HtmxCsrfExtension($tokenManager);

        $result = $extension->getCsrfHeaders();

        $this->assertStringContainsString('hx-headers=', $result);
        $this->assertStringNotContainsString("'token'", $result);
    }

    public function testDefaultTokenIdIsUsed(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->expects($this->once())
            ->method('getToken')
            ->with('custom-default')
            ->willReturn(new CsrfToken('custom-default', 'value'));

        $extension = new HtmxCsrfExtension($tokenManager, 'custom-default');

        $extension->getCsrfToken();
    }
}
