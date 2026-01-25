<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Twig;

use Mdxpl\HtmxBundle\Twig\HtmxCsrfExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class HtmxCsrfExtensionIntegrationTest extends TestCase
{
    private function createTwig(CsrfTokenManagerInterface $tokenManager): Environment
    {
        $loader = new FilesystemLoader([__DIR__ . '/../Templates']);
        $twig = new Environment($loader);
        $twig->addExtension(new HtmxCsrfExtension($tokenManager));

        return $twig;
    }

    private function createTokenManager(string $tokenValue = 'test-token-123'): CsrfTokenManagerInterface
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->method('getToken')
            ->with('mdx-htmx')
            ->willReturn(new CsrfToken('mdx-htmx', $tokenValue));

        return $tokenManager;
    }

    public function testCsrfTokenRendersTokenValue(): void
    {
        $twig = $this->createTwig($this->createTokenManager('my-secret-token'));

        $result = $twig->render('csrf.html.twig');

        self::assertStringContainsString('my-secret-token', $result);
    }

    public function testCsrfMetaRendersMetaTag(): void
    {
        $twig = $this->createTwig($this->createTokenManager('meta-token-value'));

        $template = $twig->load('csrf.html.twig');
        $result = $template->renderBlock('meta');

        self::assertSame('<meta name="csrf-token" content="meta-token-value">', $result);
    }

    public function testCsrfHeadersRendersHxHeadersAttribute(): void
    {
        $twig = $this->createTwig($this->createTokenManager('header-token-value'));

        $template = $twig->load('csrf.html.twig');
        $result = $template->renderBlock('headers');

        self::assertSame('hx-headers=\'{"X-CSRF-Token":"header-token-value"}\'', $result);
    }

    public function testCsrfMetaEscapesSpecialCharacters(): void
    {
        $twig = $this->createTwig($this->createTokenManager('token"with<special>chars'));

        $template = $twig->load('csrf.html.twig');
        $result = $template->renderBlock('meta');

        self::assertSame(
            '<meta name="csrf-token" content="token&quot;with&lt;special&gt;chars">',
            $result,
        );
    }

    public function testCsrfHeadersEscapesSingleQuotes(): void
    {
        $twig = $this->createTwig($this->createTokenManager("token'with'quotes"));

        $template = $twig->load('csrf.html.twig');
        $result = $template->renderBlock('headers');

        self::assertStringContainsString('&#039;', $result);
        self::assertStringStartsWith("hx-headers='", $result);
        self::assertStringEndsWith("'", $result);
    }

    public function testCsrfHeadersWithCustomHeaderName(): void
    {
        $tokenManager = $this->createMock(CsrfTokenManagerInterface::class);
        $tokenManager->method('getToken')
            ->willReturn(new CsrfToken('mdx-htmx', 'token-value'));

        $loader = new FilesystemLoader([__DIR__ . '/../Templates']);
        $twig = new Environment($loader);
        $twig->addExtension(new HtmxCsrfExtension($tokenManager, 'mdx-htmx', 'X-My-CSRF'));

        $template = $twig->load('csrf.html.twig');
        $result = $template->renderBlock('headers');

        self::assertSame('hx-headers=\'{"X-My-CSRF":"token-value"}\'', $result);
    }

    public function testFullPageRenderWithCsrfFunctions(): void
    {
        $twig = $this->createTwig($this->createTokenManager('full-page-token'));

        $result = $twig->render('csrf.html.twig');

        self::assertStringContainsString('full-page-token', $result);
        self::assertStringContainsString('<meta name="csrf-token"', $result);
        self::assertStringContainsString('hx-headers=', $result);
    }
}
