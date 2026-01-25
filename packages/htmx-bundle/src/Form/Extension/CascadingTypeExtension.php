<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Extension;

use InvalidArgumentException;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Adds cascading select support to Symfony forms via htmx.
 *
 * Usage:
 * ```php
 * $builder
 *     ->add('country', ChoiceType::class, [
 *         'choices' => ['USA' => 'usa', 'UK' => 'uk'],
 *         'cascading' => [
 *             'target' => 'city',
 *             'endpoint' => '/cities/{value}',  // {value} replaced with selected value
 *         ],
 *     ])
 *     ->add('city', ChoiceType::class, [
 *         'choices' => [],  // populated dynamically
 *     ]);
 * ```
 *
 * The extension:
 * 1. Sets up htmx on the source field to fetch new options on change
 * 2. Marks the target field with a wrapper ID for htmx to replace
 */
final class CascadingTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('cascading', null);
        $resolver->setAllowedTypes('cascading', ['null', 'array']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['cascading'] === null) {
            return;
        }

        /** @var array{target?: string, endpoint?: string} $config */
        $config = $options['cascading'];
        $targetFieldName = $config['target'] ?? null;
        $endpoint = $config['endpoint'] ?? null;

        if ($targetFieldName === null || $endpoint === null) {
            throw new InvalidArgumentException(
                'The "cascading" option requires both "target" and "endpoint" keys.',
            );
        }

        // Store cascading config for use in template and finishView
        $view->vars['cascading'] = [
            'target' => $targetFieldName,
            'endpoint' => $endpoint,
        ];
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        // Process cascading configuration from source fields
        $parent = $view->parent;
        if ($parent === null) {
            return;
        }

        // Check if any sibling field targets this field via cascading
        foreach ($parent->children as $siblingView) {
            $siblingCascading = $siblingView->vars['cascading'] ?? null;
            if ($siblingCascading === null || !isset($siblingCascading['target'])) {
                continue;
            }

            if ($siblingCascading['target'] === $view->vars['name']) {
                // This field is a cascading target - set wrapper ID
                /** @var string $viewId */
                $viewId = $view->vars['id'];
                $view->vars['cascading'] = [
                    'wrapper_id' => $viewId . '-wrapper',
                ];

                // Add htmx attributes to the source field
                $wrapperId = $viewId . '-wrapper';
                $endpoint = $siblingCascading['endpoint'];

                /** @var array<string, string> $existingAttrs */
                $existingAttrs = $siblingView->vars['attr'] ?? [];
                $siblingView->vars['attr'] = array_merge($existingAttrs, [
                    'hx-get' => $endpoint,
                    'hx-target' => '#' . $wrapperId,
                    'hx-trigger' => 'change',
                    'hx-on::config-request' => "if (!this.value) { event.detail.path = event.detail.path.replace('/{value}', ''); } else { event.detail.path = event.detail.path.replace('{value}', this.value); }",
                ]);
            }
        }
    }
}
