<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Htmx;

/**
 * Represents a Symfony route reference for htmx options.
 *
 * This class is used internally by HtmxOptions to store route information
 * that will be resolved to a URL by HtmxTypeExtension using the Router service.
 *
 * Supported placeholders in route parameters:
 * - {name} - replaced with the form field name
 * - {id} - replaced with the form field id
 * - {full_name} - replaced with the full form field name (e.g., 'form[email]')
 *
 * @example
 * ```php
 * // Instead of hardcoding the field name:
 * ->postRoute('app_validate', ['field' => 'email'])
 *
 * // Use placeholder:
 * ->postRoute('app_validate', ['field' => '{name}'])
 * ```
 */
final readonly class Route
{
    public const PLACEHOLDER_NAME = '{name}';
    public const PLACEHOLDER_ID = '{id}';
    public const PLACEHOLDER_FULL_NAME = '{full_name}';

    /**
     * Client-side placeholder - replaced via JavaScript when the request is made.
     * The actual value entered by the user will be substituted.
     */
    public const PLACEHOLDER_VALUE = '{value}';

    /**
     * @param string $name Route name
     * @param array<string, mixed> $params Route parameters (supports placeholders)
     */
    public function __construct(
        public string $name,
        public array $params = [],
    ) {
    }

    /**
     * Resolves placeholders in route parameters.
     *
     * @param string $fieldName Form field name
     * @param string $fieldId Form field id
     * @param string $fieldFullName Form field full name (e.g., 'form[email]')
     *
     * @return array<string, mixed> Resolved parameters
     */
    public function resolveParams(string $fieldName, string $fieldId, string $fieldFullName): array
    {
        $resolved = [];

        foreach ($this->params as $key => $value) {
            if (\is_string($value)) {
                $value = str_replace(
                    [self::PLACEHOLDER_NAME, self::PLACEHOLDER_ID, self::PLACEHOLDER_FULL_NAME],
                    [$fieldName, $fieldId, $fieldFullName],
                    $value,
                );
            }
            $resolved[$key] = $value;
        }

        return $resolved;
    }
}
