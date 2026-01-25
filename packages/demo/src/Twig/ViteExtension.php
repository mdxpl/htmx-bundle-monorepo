<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ViteExtension extends AbstractExtension
{
    /** @var array<string, array{file?: string, css?: list<string>}>|null */
    private ?array $manifest = null;

    private ?bool $devServerRunning = null;

    public function __construct(
        private readonly string $publicDir,
        private readonly bool $debug = false,
        private readonly string $devServerUrl = 'http://localhost:5173',
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
        if ($this->isDevServerRunning()) {
            // In dev mode, CSS is injected by Vite HMR via JS
            return '';
        }

        $entryData = $this->getEntry($entry);

        if ($entryData === null) {
            return '';
        }

        $tags = [];

        if (isset($entryData['css'])) {
            foreach ($entryData['css'] as $cssFile) {
                $href = '/build/' . $cssFile;
                $tags[] = \sprintf('<link rel="preload" href="%s" as="style">', $href);
                $tags[] = \sprintf('<link rel="stylesheet" href="%s">', $href);
            }
        }

        return implode("\n", $tags);
    }

    public function renderScriptTags(string $entry): string
    {
        if ($this->isDevServerRunning()) {
            return \sprintf(
                '<script type="module" src="%s/@vite/client"></script>' . "\n" .
                '<script type="module" src="%s/assets/%s.js"></script>',
                $this->devServerUrl,
                $this->devServerUrl,
                $entry,
            );
        }

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
        $tags[] = \sprintf('<link rel="modulepreload" href="%s">', $src);
        $tags[] = \sprintf('<script type="module" src="%s"></script>', $src);

        return implode("\n", $tags);
    }

    private function isDevServerRunning(): bool
    {
        if (!$this->debug) {
            return false;
        }

        if ($this->devServerRunning !== null) {
            return $this->devServerRunning;
        }

        // Check if Vite dev server is running by attempting to connect
        $handle = @fsockopen('localhost', 5173, $errno, $errstr, 0.1);

        if ($handle !== false) {
            fclose($handle);

            return $this->devServerRunning = true;
        }

        return $this->devServerRunning = false;
    }

    /** @return array{file?: string, css?: list<string>}|null */
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

    /** @return array<string, array{file?: string, css?: list<string>}> */
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

        if ($content === false) {
            return $this->manifest = [];
        }

        return $this->manifest = json_decode($content, true) ?? [];
    }
}
