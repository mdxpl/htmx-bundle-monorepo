<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\View;

use Mdxpl\HtmxBundle\Exception\BlockCannotBeSetWithoutTemplateException;

readonly class View
{
    public const RESULT_VIEW_PARAM_NAME = 'mdx_htmx_result';
    public const IS_HTMX_REQUEST_VIEW_PARAM_NAME = 'mdx_is_htmx_request';

    /**
     * @param array<string, mixed> $data
     */
    private function __construct(
        public ?string $template = null,
        public ?string $block = null,
        public array $data = [],
    ) {
        $this->assertTemplate();
    }

    private function assertTemplate(): void
    {
        if (($this->template === null || $this->template === '') && $this->block !== null) {
            throw BlockCannotBeSetWithoutTemplateException::withBlockName($this->block);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function create(string $template, ?string $block = null, array $data = []): self
    {
        if ($template === '') {
            return self::empty();
        }

        if ($block === null) {
            return self::template($template, $data);
        }

        return self::block($template, $block, $data);
    }

    public static function empty(): self
    {
        return new self();
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function template(string $template, array $data = []): self
    {
        return new self($template, null, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function block(string $template, string $block, array $data = []): self
    {
        return new self($template, $block, $data);
    }

    public function hasContent(): bool
    {
        return $this->template !== null;
    }
}
