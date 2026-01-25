<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ViteExtension extends AbstractExtension
{
    private ?array $manifest = null;

    public function __construct(
        private readonly string $publicDir,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('vite_entry_link_tags', $this->renderLinkTags(...), ['is_safe' => ['html']]),
            new TwigFunction('vite_entry_script_tags', $this->renderScriptTags(...), ['is_safe' => ['html']]),
        ];
    }

    public function renderLinkTags(string $entry): string
    {
        $entryData = $this->getEntry($entry);

        if ($entryData === null) {
            return '';
        }

        $tags = [];

        if (isset($entryData['css'])) {
            foreach ($entryData['css'] as $cssFile) {
                $href = '/build/' . $cssFile;
                $tags[] = sprintf('<link rel="preload" href="%s" as="style">', $href);
                $tags[] = sprintf('<link rel="stylesheet" href="%s">', $href);
            }
        }

        return implode("\n", $tags);
    }

    public function renderScriptTags(string $entry): string
    {
        $entryData = $this->getEntry($entry);

        if ($entryData === null) {
            return '';
        }

        $file = $entryData['file'] ?? null;

        if ($file === null) {
            return '';
        }

        $src = '/build/' . $file;
        $tags = [];
        $tags[] = sprintf('<link rel="modulepreload" href="%s">', $src);
        $tags[] = sprintf('<script type="module" src="%s"></script>', $src);

        return implode("\n", $tags);
    }

    private function getEntry(string $entry): ?array
    {
        $manifest = $this->getManifest();

        // Try exact match first
        if (isset($manifest[$entry])) {
            return $manifest[$entry];
        }

        // Try with assets/ prefix
        $key = 'assets/' . $entry . '.js';
        if (isset($manifest[$key])) {
            return $manifest[$key];
        }

        return null;
    }

    private function getManifest(): array
    {
        if ($this->manifest !== null) {
            return $this->manifest;
        }

        $manifestPath = $this->publicDir . '/build/.vite/manifest.json';

        if (!file_exists($manifestPath)) {
            return $this->manifest = [];
        }

        $content = file_get_contents($manifestPath);

        return $this->manifest = json_decode($content, true) ?? [];
    }
}
