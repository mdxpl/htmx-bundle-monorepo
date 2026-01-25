<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Extension;

use Mdxpl\HtmxBundle\Form\Htmx\HtmxOptions;
use Mdxpl\HtmxBundle\Form\Htmx\Route;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Form extension that adds HTMX attributes support to all form fields.
 *
 * Usage with array:
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
 *
 * Usage with HtmxOptions builder:
 * ```php
 * use Mdxpl\HtmxBundle\Form\Htmx\HtmxOptions;
 * use Mdxpl\HtmxBundle\Form\Htmx\Trigger\Trigger;
 *
 * $builder->add('search', TextType::class, [
 *     'htmx' => HtmxOptions::create()
 *         ->get('/search')
 *         ->trigger(Trigger::keyup()->changed()->delay(300))
 *         ->target('#results')
 *         ->indicator('#spinner'),
 * ]);
 * ```
 *
 * Usage with routes:
 * ```php
 * $builder->add('search', TextType::class, [
 *     'htmx' => HtmxOptions::create()
 *         ->getRoute('app_search', ['query' => 'foo'])
 *         ->trigger(Trigger::keyup()->changed()->delay(300))
 *         ->target('#results'),
 * ]);
 * ```
 */
final class HtmxTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private readonly ?UrlGeneratorInterface $urlGenerator = null,
    ) {
    }

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
        $resolver->setAllowedTypes('htmx', ['null', 'array', HtmxOptions::class]);
        $resolver->setDefault('htmx', null);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if (null === $options['htmx']) {
            return;
        }

        /** @var array<string, mixed>|HtmxOptions $htmx */
        $htmx = $options['htmx'];

        /** @var array<string, mixed> $htmxOptions */
        $htmxOptions = $htmx instanceof HtmxOptions ? $htmx->toArray() : $htmx;

        /** @var array<string, string> $attrs */
        $attrs = $view->vars['attr'] ?? [];

        // Extract form field info for placeholder resolution
        /** @var string $fieldName */
        $fieldName = $view->vars['name'];
        /** @var string $fieldId */
        $fieldId = $view->vars['id'];
        /** @var string $fieldFullName */
        $fieldFullName = $view->vars['full_name'];

        $hasValuePlaceholder = false;

        foreach ($htmxOptions as $key => $value) {
            if (null === $value) {
                continue;
            }

            $attributeName = $this->resolveAttributeName($key);
            $formattedValue = $this->formatAttributeValue($value, $fieldName, $fieldId, $fieldFullName);

            // Check if {value} placeholder is used (needs client-side resolution)
            if (str_contains($formattedValue, Route::PLACEHOLDER_VALUE)) {
                $hasValuePlaceholder = true;
            }

            $attrs[$attributeName] = $formattedValue;
        }

        // Auto-add config-request handler for {value} placeholder resolution
        if ($hasValuePlaceholder && !isset($attrs['hx-on::config-request'])) {
            $attrs['hx-on::config-request'] = sprintf(
                "event.detail.path = event.detail.path.replace('%s', encodeURIComponent(this.value))",
                Route::PLACEHOLDER_VALUE,
            );
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
     * Formats an attribute value for output.
     *
     * @param mixed $value The value to format
     * @param string $fieldName Form field name (for placeholder resolution)
     * @param string $fieldId Form field id (for placeholder resolution)
     * @param string $fieldFullName Form field full name (for placeholder resolution)
     */
    private function formatAttributeValue(
        mixed $value,
        string $fieldName = '',
        string $fieldId = '',
        string $fieldFullName = '',
    ): string {
        // Handle Route objects - resolve placeholders and generate URL
        if ($value instanceof Route) {
            if ($this->urlGenerator === null) {
                throw new \LogicException(
                    'Cannot use route references in htmx options without UrlGeneratorInterface. '
                    . 'Make sure the router service is available.',
                );
            }

            $resolvedParams = $value->resolveParams($fieldName, $fieldId, $fieldFullName);

            return $this->urlGenerator->generate($value->name, $resolvedParams);
        }

        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (\is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        }

        if (\is_string($value)) {
            // Resolve placeholders in string values
            return str_replace(
                [Route::PLACEHOLDER_NAME, Route::PLACEHOLDER_ID, Route::PLACEHOLDER_FULL_NAME],
                [$fieldName, $fieldId, $fieldFullName],
                $value,
            );
        }

        if (\is_int($value) || \is_float($value)) {
            return (string) $value;
        }

        if (\is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return '';
    }
}
