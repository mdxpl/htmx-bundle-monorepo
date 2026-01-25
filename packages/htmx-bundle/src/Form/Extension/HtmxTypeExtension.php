<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form extension that adds HTMX attributes support to all form fields.
 *
 * Usage:
 * ```php
 * $builder->add('search', TextType::class, [
 *     'htmx' => [
 *         'get' => '/search',
 *         'trigger' => 'keyup changed delay:300ms',
 *         'target' => '#results',
 *         'indicator' => '#spinner',
 *     ],
 * ]);
 * ```
 */
final class HtmxTypeExtension extends AbstractTypeExtension
{
    private const HTMX_ATTRIBUTE_MAP = [
        'get' => 'hx-get',
        'post' => 'hx-post',
        'put' => 'hx-put',
        'patch' => 'hx-patch',
        'delete' => 'hx-delete',
        'trigger' => 'hx-trigger',
        'target' => 'hx-target',
        'swap' => 'hx-swap',
        'indicator' => 'hx-indicator',
        'select' => 'hx-select',
        'select-oob' => 'hx-select-oob',
        'vals' => 'hx-vals',
        'confirm' => 'hx-confirm',
        'include' => 'hx-include',
        'params' => 'hx-params',
        'push-url' => 'hx-push-url',
        'boost' => 'hx-boost',
        'sync' => 'hx-sync',
        'validate' => 'hx-validate',
        'disable' => 'hx-disable',
        'disabled-elt' => 'hx-disabled-elt',
        'disinherit' => 'hx-disinherit',
        'encoding' => 'hx-encoding',
        'ext' => 'hx-ext',
        'headers' => 'hx-headers',
        'history' => 'hx-history',
        'history-elt' => 'hx-history-elt',
        'inherit' => 'hx-inherit',
        'preserve' => 'hx-preserve',
        'prompt' => 'hx-prompt',
        'replace-url' => 'hx-replace-url',
        'request' => 'hx-request',
    ];

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('htmx');
        $resolver->setAllowedTypes('htmx', ['null', 'array']);
        $resolver->setDefault('htmx', null);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (null === $options['htmx']) {
            return;
        }

        /** @var array<string, mixed> $htmxOptions */
        $htmxOptions = $options['htmx'];

        /** @var array<string, string> $attrs */
        $attrs = $view->vars['attr'] ?? [];

        foreach ($htmxOptions as $key => $value) {
            if (null === $value) {
                continue;
            }

            $attributeName = $this->resolveAttributeName($key);
            $attrs[$attributeName] = $this->formatAttributeValue($value);
        }

        $view->vars['attr'] = $attrs;
    }

    private function resolveAttributeName(string $key): string
    {
        // Handle event handlers: on::{event} → hx-on::{event}
        if (str_starts_with($key, 'on::') || str_starts_with($key, 'on:')) {
            return 'hx-' . $key;
        }

        // Handle mapped attributes
        if (isset(self::HTMX_ATTRIBUTE_MAP[$key])) {
            return self::HTMX_ATTRIBUTE_MAP[$key];
        }

        // Allow raw hx-* attributes to pass through
        if (str_starts_with($key, 'hx-')) {
            return $key;
        }

        // Default: prefix with hx-
        return 'hx-' . $key;
    }

    /**
     * @param mixed $value
     */
    private function formatAttributeValue($value): string
    {
        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (\is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        }

        if (\is_string($value) || \is_int($value) || \is_float($value)) {
            return (string) $value;
        }

        if (\is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return '';
    }
}
