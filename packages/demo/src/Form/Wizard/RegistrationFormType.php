<?php

declare(strict_types=1);

namespace App\Form\Wizard;

use App\Form\Wizard\Step\AccountStepType;
use App\Form\Wizard\Step\ConfirmationStepType;
use App\Form\Wizard\Step\PreferencesStepType;
use App\Form\Wizard\Step\ProfileStepType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Registration form type for the wizard demo.
 * Each step is a separate subform for better organization and validation.
 * Only the current step is validated - other subforms have validation disabled.
 *
 * @extends AbstractType<array<string, mixed>>
 */
class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Determine current step from wizard options
        $currentStepName = null;
        if (isset($options['wizard']['schema'], $options['wizard']['state'])) {
            $currentStepIndex = $options['wizard']['state']->getCurrentStep();
            $currentStepName = $options['wizard']['schema']->getStep($currentStepIndex)->name;
        }

        // Only validate the current step's subform
        $builder
            ->add('account', AccountStepType::class, [
                'validation_groups' => $currentStepName === 'account' ? ['Default'] : false,
            ])
            ->add('profile', ProfileStepType::class, [
                'validation_groups' => $currentStepName === 'profile' ? ['Default'] : false,
            ])
            ->add('preferences', PreferencesStepType::class, [
                'validation_groups' => $currentStepName === 'preferences' ? ['Default'] : false,
            ])
            ->add('confirmation', ConfirmationStepType::class, [
                'validation_groups' => $currentStepName === 'confirmation' ? ['Default'] : false,
            ])
            ->add('_submitted', HiddenType::class, [
                'data' => '1',
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'wizard' => null,
        ]);

        $resolver->setAllowedTypes('wizard', ['null', 'array']);
    }
}
