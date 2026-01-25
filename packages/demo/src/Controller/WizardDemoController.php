<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\Wizard\RegistrationFormType;
use App\Form\Wizard\RegistrationWizardSchema;
use Mdxpl\HtmxBundle\Form\Wizard\NavigationStrategy;
use Mdxpl\HtmxBundle\Form\Wizard\WizardHelper;
use Mdxpl\HtmxBundle\Form\Wizard\WizardSchema;
use Mdxpl\HtmxBundle\Form\Wizard\WizardState;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/wizard')]
final class WizardDemoController extends AbstractController
{
    private const TEMPLATE = 'wizard_demo.html.twig';

    public function __construct(
        private readonly WizardHelper $wizard,
    ) {
    }

    #[Route('', name: 'app_wizard', methods: ['GET', 'POST'])]
    #[Route('/{step}', name: 'app_wizard_step', methods: ['GET', 'POST'], requirements: ['step' => 'account|profile|preferences|confirmation'])]
    public function index(HtmxRequest $htmx, Request $request, ?string $step = null): HtmxResponse
    {
        $schema = $this->createSchema($request);
        $state = $this->wizard->loadOrStart($schema);

        if ($step !== null && $request->isMethod('GET')) {
            $this->wizard->goToStep($schema, $state, $schema->getStepIndex($step));
        }

        $form = $this->createWizardForm($schema, $state);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->renderStep($htmx, $schema, $state, $form);
        }

        return $this->handleSubmission($htmx, $request, $schema, $state, $form);
    }

    #[Route('/reset', name: 'app_wizard_reset', methods: ['POST'])]
    public function reset(HtmxRequest $htmx): HtmxResponse
    {
        $this->wizard->clear(RegistrationWizardSchema::create());

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->redirect($this->generateUrl('app_wizard'))
            ->build();
    }

    private function createSchema(Request $request): WizardSchema
    {
        $strategyValue = $request->query->getString('_nav', '');
        $strategy = NavigationStrategy::tryFrom($strategyValue) ?? NavigationStrategy::FREE;

        return RegistrationWizardSchema::create($strategy);
    }

    /**
     * @return FormInterface<array<string, mixed>>
     */
    private function createWizardForm(WizardSchema $schema, WizardState $state): FormInterface
    {
        return $this->createForm(RegistrationFormType::class, $state->getAllData(), [
            'wizard' => ['schema' => $schema, 'state' => $state],
        ]);
    }

    /**
     * @param FormInterface<array<string, mixed>> $form
     */
    private function handleSubmission(
        HtmxRequest $htmx,
        Request $request,
        WizardSchema $schema,
        WizardState $state,
        FormInterface $form,
    ): HtmxResponse {
        $action = $request->request->getString('_wizard_action', 'next');

        if ($action === 'back') {
            $this->wizard->previousStep($schema, $state);

            return $this->renderStep($htmx, $schema, $state, $form);
        }

        if (!$form->isValid()) {
            $this->wizard->setStepErrors($schema, $state, $form);

            return $this->renderStep($htmx, $schema, $state, $form, hasErrors: true);
        }

        $this->wizard->saveStepData($schema, $state, $form->getData());
        $this->wizard->markStepCompleted($schema, $state);

        if ($state->isLastStep($schema) && $action === 'submit') {
            return $this->handleFinalSubmission($htmx, $schema, $state, $form);
        }

        $this->wizard->nextStep($schema, $state);

        return $this->renderStep($htmx, $schema, $state, $form);
    }

    /**
     * @param FormInterface<array<string, mixed>> $form
     */
    private function handleFinalSubmission(
        HtmxRequest $htmx,
        WizardSchema $schema,
        WizardState $state,
        FormInterface $form,
    ): HtmxResponse {
        $incompleteSteps = $this->findIncompleteSteps($schema, $state);

        if ($incompleteSteps !== []) {
            $state->setCurrentStep($schema->getStepIndex($incompleteSteps[0]));
            $this->wizard->save($schema, $state);

            return $this->renderStep($htmx, $schema, $state, $form, hasErrors: true);
        }

        $allData = $state->getAllData();
        $this->wizard->clear($schema);

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock(self::TEMPLATE, 'success', ['data' => $allData])
            ->build();
    }

    /**
     * @return string[] Names of incomplete steps
     */
    private function findIncompleteSteps(WizardSchema $schema, WizardState $state): array
    {
        $incomplete = [];

        foreach ($schema->getSteps() as $index => $step) {
            if ($index >= $state->getCurrentStep()) {
                break;
            }

            if (!$state->isStepCompleted($step->name)) {
                $incomplete[] = $step->name;
                $state->setStepErrors($step->name, ['_form' => ['Please complete this step']]);
            }
        }

        return $incomplete;
    }

    /**
     * @param FormInterface<array<string, mixed>> $form
     */
    private function renderStep(
        HtmxRequest $htmx,
        WizardSchema $schema,
        WizardState $state,
        FormInterface $form,
        bool $hasErrors = false,
    ): HtmxResponse {
        $currentStep = $schema->getStep($state->getCurrentStep());

        $formToRender = $hasErrors ? $form : $this->createWizardForm($schema, $state);

        $viewData = [
            'form' => $formToRender->createView(),
            'wizard_schema' => $schema,
            'wizard_state' => $state,
            'wizard_current_step' => $currentStep,
            'navigation_strategies' => NavigationStrategy::cases(),
            'current_navigation_strategy' => $schema->getNavigationStrategy(),
        ];

        $builder = HtmxResponseBuilder::create($htmx->isHtmx);

        if (!$htmx->isHtmx) {
            return $builder->success()->view(self::TEMPLATE, $viewData)->build();
        }

        $stepUrl = $this->generateStepUrl($currentStep->name, $schema->getNavigationStrategy());
        $builder->pushUrl($stepUrl);

        if ($hasErrors) {
            return $builder
                ->failure()
                ->triggerAfterSwap(['scrollTo' => '#wizard-tabs'])
                ->viewBlock(self::TEMPLATE, 'wizard', $viewData)
                ->build();
        }

        return $builder->success()->viewBlock(self::TEMPLATE, 'wizard', $viewData)->build();
    }

    private function generateStepUrl(string $stepName, NavigationStrategy $strategy): string
    {
        $params = ['step' => $stepName];

        if ($strategy !== NavigationStrategy::FREE) {
            $params['_nav'] = $strategy->value;
        }

        return $this->generateUrl('app_wizard_step', $params);
    }
}
