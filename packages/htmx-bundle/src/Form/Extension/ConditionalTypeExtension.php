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
 * Adds conditional field support to Symfony forms via htmx.
 *
 * Usage:
 * ```php
 * $builder
 *     ->add('accountType', ChoiceType::class, [
 *         'choices' => ['Personal' => 'personal', 'Business' => 'business'],
 *         'expanded' => true,
 *     ])
 *     ->add('business', BusinessFieldsType::class, [
 *         'conditional' => [
 *             'trigger' => 'accountType',  // which field triggers the condition
 *             'endpoint' => '/form/business-fields',  // where to fetch content
 *         ],
 *     ]);
 * ```
 *
 * This sets up htmx attributes on the trigger field to load the conditional
 * field content when changed. Works with both regular and expanded choice types.
 */
final class ConditionalTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('conditional', null);
        $resolver->setAllowedTypes('conditional', ['null', 'array']);
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['conditional'] === null) {
            return;
        }

        /** @var array{trigger?: string, endpoint?: string} $config */
        $config = $options['conditional'];
        $triggerFieldName = $config['trigger'] ?? null;
        $endpoint = $config['endpoint'] ?? null;

        if ($triggerFieldName === null || $endpoint === null) {
            throw new InvalidArgumentException(
                'The "conditional" option requires both "trigger" and "endpoint" keys.',
            );
        }

        $parent = $view->parent;
        if ($parent === null || !isset($parent->children[$triggerFieldName])) {
            throw new InvalidArgumentException(\sprintf(
                'Trigger field "%s" not found in the parent form.',
                $triggerFieldName,
            ));
        }

        // Set the wrapper ID for this conditional field
        /** @var string $viewId */
        $viewId = $view->vars['id'];
        $targetId = 'conditional-' . $viewId;
        $view->vars['conditional'] = [
            'wrapper_id' => $targetId,
            'trigger' => $triggerFieldName,
            'endpoint' => $endpoint,
        ];

        $triggerView = $parent->children[$triggerFieldName];
        /** @var string $triggerWrapperId */
        $triggerWrapperId = $triggerView->vars['id'];

        // Check if trigger is an expanded choice (multiple inputs like radio/checkbox)
        /** @var bool $isExpanded */
        $isExpanded = $triggerView->vars['expanded'] ?? false;

        if ($isExpanded) {
            // For expanded choice types (radio/checkbox), add htmx to each child input
            foreach ($triggerView->children as $childView) {
                /** @var array<string, string> $childAttrs */
                $childAttrs = $childView->vars['attr'] ?? [];
                $childView->vars['attr'] = array_merge($childAttrs, [
                    'hx-get' => $endpoint,
                    'hx-target' => '#' . $targetId,
                    'hx-trigger' => 'change',
                    'hx-include' => '#' . $triggerWrapperId . ' input:checked',
                ]);
            }
        } else {
            // For regular select/input, add htmx directly to the element
            /** @var array<string, string> $existingAttrs */
            $existingAttrs = $triggerView->vars['attr'] ?? [];
            $triggerView->vars['attr'] = array_merge($existingAttrs, [
                'hx-get' => $endpoint,
                'hx-target' => '#' . $targetId,
                'hx-trigger' => 'change',
                'hx-include' => 'this',
            ]);
        }
    }
}
