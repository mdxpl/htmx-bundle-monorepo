<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Extension;

use Mdxpl\HtmxBundle\Form\Wizard\WizardSchema;
use Mdxpl\HtmxBundle\Form\Wizard\WizardState;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form extension that adds wizard/multi-step form support.
 *
 * Usage:
 * ```php
 * $form = $this->createForm(RegistrationFormType::class, $state->getAllData(), [
 *     'wizard' => [
 *         'schema' => $schema,
 *         'state' => $state,
 *     ],
 *     'validation_groups' => $schema->getValidationGroups($currentStep->name),
 * ]);
 * ```
 *
 * In Twig templates, access wizard data via form.vars.wizard:
 * - form.vars.wizard.schema: WizardSchema instance
 * - form.vars.wizard.state: WizardState instance
 * - form.vars.wizard.steps: Array of step view data
 * - form.vars.wizard.current_step: Current step index
 * - form.vars.wizard.is_first_step: Boolean
 * - form.vars.wizard.is_last_step: Boolean
 */
final class WizardTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('wizard');
        $resolver->setAllowedTypes('wizard', ['null', 'array']);
        $resolver->setDefault('wizard', null);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['wizard'] === null) {
            return;
        }

        // Only apply to root form
        if ($form->getParent() !== null) {
            return;
        }

        /** @var array{schema: WizardSchema, state: WizardState} $wizardConfig */
        $wizardConfig = $options['wizard'];

        $schema = $wizardConfig['schema'];
        $state = $wizardConfig['state'];

        $view->vars['wizard'] = [
            'schema' => $schema,
            'state' => $state,
            'steps' => $this->buildStepsViewData($schema, $state),
            'current_step' => $state->getCurrentStep(),
            'is_first_step' => $state->isFirstStep($schema),
            'is_last_step' => $state->isLastStep($schema),
            'navigation_strategy' => $schema->getNavigationStrategy()->value,
        ];
    }

    /**
     * Build view data for each step.
     *
     * @return array<int, array{
     *     name: string,
     *     label: string,
     *     index: int,
     *     is_current: bool,
     *     is_completed: bool,
     *     has_errors: bool,
     *     errors: array<string, string[]>,
     *     can_navigate: bool,
     *     allow_back: bool
     * }>
     */
    private function buildStepsViewData(WizardSchema $schema, WizardState $state): array
    {
        $steps = [];
        $currentStep = $state->getCurrentStep();

        foreach ($schema->getSteps() as $index => $step) {
            $isCompleted = $state->isStepCompleted($step->name);

            $steps[] = [
                'name' => $step->name,
                'label' => $step->label,
                'index' => $index,
                'is_current' => $index === $currentStep,
                'is_completed' => $isCompleted,
                'has_errors' => $state->hasStepErrors($step->name),
                'errors' => $state->getStepErrors($step->name),
                'can_navigate' => $schema->canNavigateToStep($index, $currentStep, $state),
                'allow_back' => $step->allowBack,
            ];
        }

        return $steps;
    }
}
