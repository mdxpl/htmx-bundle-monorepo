<?php

declare(strict_types=1);

namespace App\Controller;

use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Route('/form', name: 'app_form', methods: ['GET', 'POST'])]
final class FormController extends AbstractController
{
    public function __invoke(HtmxRequest $htmx, Request $request): HtmxResponse
    {
        $template = 'form.html.twig';

        $form = $this->createFormBuilder()
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(message: 'Name cannot be blank'),
                    new Length(min: 2, minMessage: 'Name must be at least {{ limit }} characters'),
                ],
                'required' => false,
                'attr' => ['placeholder' => 'Enter your name...'],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Submit'])
            ->getForm()
            ->handleRequest($request);

        $viewData = ['form' => $form->createView()];
        $builder = HtmxResponseBuilder::create($htmx->isHtmx);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $name = $form->get('name')->getData();
                return $builder
                    ->success()
                    ->viewBlock($template, 'successComponent', ['name' => $name])
                    ->build();
            }

            return $builder
                ->failure()
                ->viewBlock($template, 'failureComponent', $viewData)
                ->build();
        }

        if ($htmx->isHtmx) {
            return $builder
                ->success()
                ->viewBlock($template, 'formComponent', $viewData)
                ->build();
        }

        return $builder
            ->success()
            ->view($template, $viewData)
            ->build();
    }
}
