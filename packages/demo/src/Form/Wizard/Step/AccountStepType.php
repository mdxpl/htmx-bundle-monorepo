<?php

declare(strict_types=1);

namespace App\Form\Wizard\Step;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class AccountStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'required' => false,
                'attr' => ['placeholder' => 'Enter your email...'],
                'constraints' => [
                    new NotBlank(message: 'Email is required'),
                    new Email(message: 'Please enter a valid email'),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'required' => false,
                'always_empty' => false,
                'attr' => ['placeholder' => 'Create a password...'],
                'constraints' => [
                    new NotBlank(message: 'Password is required'),
                    new Length(
                        min: 8,
                        minMessage: 'Password must be at least {{ limit }} characters',
                    ),
                ],
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'Confirm Password',
                'required' => false,
                'always_empty' => false,
                'attr' => ['placeholder' => 'Confirm your password...'],
                'constraints' => [
                    new NotBlank(message: 'Please confirm your password'),
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
