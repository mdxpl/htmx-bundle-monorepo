<?php

declare(strict_types=1);

namespace App\Controller;

use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form Demo - showcases the DaisyUI form theme.
 *
 * This demonstrates how forms render with the daisyui_htmx_layout theme
 * using simple {{ form(form) }} or {{ form_row() }} calls.
 */
#[Route('/form', name: 'app_form', methods: ['GET', 'POST'])]
final class FormController extends AbstractController
{
    public function __invoke(HtmxRequest $htmx, Request $request): HtmxResponse
    {
        $form = $this->createRegistrationForm();
        $form->handleRequest($request);

        $template = 'form.html.twig';
        $builder = HtmxResponseBuilder::create($htmx->isHtmx);

        if ($form->isSubmitted() && $form->isValid()) {
            return $builder
                ->success()
                ->viewBlock($template, 'success', ['data' => $form->getData()])
                ->build();
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            return $builder
                ->failure()
                ->viewBlock($template, 'formContent', ['form' => $form->createView()])
                ->build();
        }

        if ($htmx->isHtmx) {
            return $builder
                ->success()
                ->viewBlock($template, 'formContent', ['form' => $form->createView()])
                ->build();
        }

        return $builder
            ->success()
            ->view($template, ['form' => $form->createView()])
            ->build();
    }

    /**
     * @return FormInterface<array<string, mixed>>
     */
    private function createRegistrationForm(): FormInterface
    {
        return $this->createFormBuilder()
            ->add('name', TextType::class, [
                'label' => 'Full Name',
                'help' => 'Enter your full name as it appears on official documents.',
                'constraints' => [
                    new NotBlank(message: 'Name is required'),
                    new Length(min: 2, max: 100),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'constraints' => [
                    new NotBlank(message: 'Email is required'),
                    new Email(message: 'Please enter a valid email'),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'help' => 'At least 8 characters.',
                'constraints' => [
                    new NotBlank(message: 'Password is required'),
                    new Length(min: 8, minMessage: 'Password must be at least 8 characters'),
                ],
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Bio',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('country', ChoiceType::class, [
                'label' => 'Country',
                'choices' => [
                    'Select a country...' => '',
                    'United States' => 'us',
                    'United Kingdom' => 'uk',
                    'Germany' => 'de',
                    'France' => 'fr',
                    'Poland' => 'pl',
                ],
                'constraints' => [
                    new NotBlank(message: 'Please select a country'),
                ],
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Role',
                'expanded' => true,
                'choices' => [
                    'User' => 'user',
                    'Admin' => 'admin',
                    'Moderator' => 'moderator',
                ],
                'data' => 'user',
            ])
            ->add('notifications', ChoiceType::class, [
                'label' => 'Notifications',
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                    'Email notifications' => 'email',
                    'SMS notifications' => 'sms',
                    'Push notifications' => 'push',
                ],
            ])
            ->add('terms', CheckboxType::class, [
                'label' => 'I agree to the terms and conditions',
                'constraints' => [
                    new NotBlank(message: 'You must accept the terms'),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Register',
            ])
            ->getForm();
    }
}
