<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class SourceCodeExtension extends AbstractExtension
{
    public function __construct(
        private readonly string $projectDir,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('source_code', $this->getSourceCode(...)),
            new TwigFunction('github_url', $this->getGithubUrl(...)),
        ];
    }

    public function getSourceCode(string $relativePath): string
    {
        $fullPath = $this->projectDir . '/' . ltrim($relativePath, '/');

        if (!file_exists($fullPath)) {
            return "// File not found: {$relativePath}";
        }

        $content = file_get_contents($fullPath);

        return $content !== false ? $content : '';
    }

    public function getGithubUrl(string $relativePath, string $repo = 'mdxpl/htmx-bundle-demo'): string
    {
        return \sprintf(
            'https://github.com/%s/blob/main/%s',
            $repo,
            ltrim($relativePath, '/'),
        );
    }
}
