<?php

declare(strict_types=1);

namespace App\Form;

use Mdxpl\HtmxBundle\Form\Htmx\HtmxOptions;
use Mdxpl\HtmxBundle\Form\Htmx\SwapStyle;
use Mdxpl\HtmxBundle\Form\Htmx\Trigger\Trigger;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * @extends AbstractType<array<string, mixed>>
 */
final class BusinessFieldsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var bool $isRequired */
        $isRequired = $options['is_required'];

        $builder
            ->add('companyName', TextType::class, [
                'label' => 'Company Name',
                'required' => $isRequired,
                'attr' => ['placeholder' => 'Enter company name...'],
                'constraints' => $isRequired === true ? [
                    new NotBlank(message: 'Company name is required'),
                    new Length(min: 2, max: 100, minMessage: 'Company name must be at least {{ limit }} characters'),
                ] : [],
                'htmx' => $isRequired === true
                    ? HtmxOptions::create()
                        ->postRoute('app_advanced_form_validate_business', ['field' => '{name}'])
                        ->trigger(Trigger::blur()->changed()->delay(500))
                        ->target('#{name}-validation')
                        ->swap(SwapStyle::InnerHTML)
                    : null,
            ])
            ->add('taxId', TextType::class, [
                'label' => 'Tax ID / VAT Number',
                'required' => $isRequired,
                'attr' => ['placeholder' => 'Enter tax ID...'],
                'constraints' => $isRequired === true ? [
                    new NotBlank(message: 'Tax ID is required'),
                    new Regex(
                        pattern: '/^[A-Z]{2}[0-9A-Z]{8,12}$/',
                        message: 'Tax ID must be in format: 2 letters followed by 8-12 alphanumeric characters (e.g., PL1234567890)',
                    ),
                ] : [],
                'htmx' => $isRequired === true
                    ? HtmxOptions::create()
                        ->postRoute('app_advanced_form_validate_business', ['field' => '{name}'])
                        ->trigger(Trigger::blur()->changed()->delay(500))
                        ->target('#{name}-validation')
                        ->swap(SwapStyle::InnerHTML)
                    : null,
            ])
            ->add('companyAddress', TextareaType::class, [
                'label' => 'Company Address',
                'required' => false,
                'attr' => ['placeholder' => 'Enter company address...', 'rows' => 2],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'is_required' => false,
        ]);
        $resolver->setAllowedTypes('is_required', 'bool');
    }
}
