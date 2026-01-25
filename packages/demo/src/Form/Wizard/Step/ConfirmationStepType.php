<?php

declare(strict_types=1);

namespace App\Form\Wizard\Step;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class ConfirmationStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('confirm', CheckboxType::class, [
                'label' => 'I confirm that all information provided is accurate',
                'required' => false,
                'constraints' => [
                    new IsTrue(message: 'Please confirm your registration'),
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
