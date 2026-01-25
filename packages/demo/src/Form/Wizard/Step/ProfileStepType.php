<?php

declare(strict_types=1);

namespace App\Form\Wizard\Step;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class ProfileStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'required' => false,
                'attr' => ['placeholder' => 'Enter your first name...'],
                'constraints' => [
                    new NotBlank(message: 'First name is required'),
                    new Length(
                        min: 2,
                        max: 50,
                        minMessage: 'First name must be at least {{ limit }} characters',
                        maxMessage: 'First name cannot exceed {{ limit }} characters',
                    ),
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'required' => false,
                'attr' => ['placeholder' => 'Enter your last name...'],
                'constraints' => [
                    new NotBlank(message: 'Last name is required'),
                    new Length(
                        min: 2,
                        max: 50,
                        minMessage: 'Last name must be at least {{ limit }} characters',
                        maxMessage: 'Last name cannot exceed {{ limit }} characters',
                    ),
                ],
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Bio (Optional)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Tell us about yourself...',
                    'rows' => 4,
                ],
                'constraints' => [
                    new Length(
                        max: 500,
                        maxMessage: 'Bio cannot exceed {{ limit }} characters',
                    ),
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
