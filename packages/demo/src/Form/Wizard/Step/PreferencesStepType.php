<?php

declare(strict_types=1);

namespace App\Form\Wizard\Step;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class PreferencesStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('newsletter', CheckboxType::class, [
                'label' => 'Subscribe to newsletter',
                'required' => false,
                'help' => 'Receive updates about new features and promotions',
            ])
            ->add('theme', ChoiceType::class, [
                'label' => 'Preferred Theme',
                'required' => false,
                'choices' => [
                    'System Default' => 'system',
                    'Light' => 'light',
                    'Dark' => 'dark',
                ],
                'expanded' => true,
                'data' => 'system',
            ])
            ->add('notifications', ChoiceType::class, [
                'label' => 'Notification Preferences',
                'required' => false,
                'choices' => [
                    'All notifications' => 'all',
                    'Important only' => 'important',
                    'None' => 'none',
                ],
                'expanded' => false,
                'placeholder' => 'Select notification preference...',
                'constraints' => [
                    new NotBlank(message: 'Please select a notification preference'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
        ]);
    }
}
