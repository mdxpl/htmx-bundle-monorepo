<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Twig;

use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HtmxCsrfExtension extends AbstractExtension
{
    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly string $tokenId = 'mdx-htmx',
        private readonly string $headerName = 'X-CSRF-Token',
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('htmx_csrf_token', $this->getCsrfToken(...)),
            new TwigFunction('htmx_csrf_meta', $this->getCsrfMeta(...), ['is_safe' => ['html']]),
            new TwigFunction('htmx_csrf_headers', $this->getCsrfHeaders(...), ['is_safe' => ['html']]),
        ];
    }

    public function getCsrfToken(): string
    {
        return $this->csrfTokenManager->getToken($this->tokenId)->getValue();
    }

    public function getCsrfMeta(): string
    {
        return \sprintf('<meta name="csrf-token" content="%s">', htmlspecialchars($this->getCsrfToken(), ENT_QUOTES, 'UTF-8'));
    }

    public function getCsrfHeaders(): string
    {
        $headers = json_encode([$this->headerName => $this->getCsrfToken()], JSON_THROW_ON_ERROR);

        return \sprintf("hx-headers='%s'", str_replace("'", '&#039;', $headers));
    }
}
