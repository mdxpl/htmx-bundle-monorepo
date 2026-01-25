<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Integration;

use Mdxpl\HtmxBundle\Form\Extension\WizardTypeExtension;
use Mdxpl\HtmxBundle\Form\Wizard\WizardSchema;
use Mdxpl\HtmxBundle\Form\Wizard\WizardState;
use Mdxpl\HtmxBundle\Form\Wizard\WizardStep;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormView;

/**
 * Integration tests for WizardTypeExtension.
 */
class WizardTypeExtensionIntegrationTest extends TestCase
{
    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addTypeExtension(new WizardTypeExtension())
            ->getFormFactory();
    }

    private function createSchema(): WizardSchema
    {
        return new WizardSchema(
            name: 'test',
            version: '1.0',
            steps: [
                new WizardStep(
                    name: 'step1',
                    label: 'First Step',
                    validationGroups: ['step1_validation'],
                ),
                new WizardStep(
                    name: 'step2',
                    label: 'Second Step',
                    allowBack: false,
                ),
                new WizardStep(
                    name: 'step3',
                    label: 'Third Step',
                ),
            ],
        );
    }

    private function createFormView(WizardSchema $schema, WizardState $state): FormView
    {
        $form = $this->formFactory->createBuilder(FormType::class, null, [
            'wizard' => ['schema' => $schema, 'state' => $state],
        ])
            ->add('field', TextType::class)
            ->getForm();

        return $form->createView();
    }

    public function testNoWizardOptionDoesNotAddVars(): void
    {
        $form = $this->formFactory->createBuilder(FormType::class)
            ->add('field', TextType::class)
            ->getForm();

        $view = $form->createView();

        self::assertArrayNotHasKey('wizard', $view->vars);
    }

    public function testNullWizardOptionDoesNotAddVars(): void
    {
        $form = $this->formFactory->createBuilder(FormType::class, null, [
            'wizard' => null,
        ])
            ->add('field', TextType::class)
            ->getForm();

        $view = $form->createView();

        self::assertArrayNotHasKey('wizard', $view->vars);
    }

    public function testWizardVarsAreSet(): void
    {
        $schema = $this->createSchema();
        $state = new WizardState('1.0');

        $view = $this->createFormView($schema, $state);

        self::assertArrayHasKey('wizard', $view->vars);
        self::assertSame($schema, $view->vars['wizard']['schema']);
        self::assertSame($state, $view->vars['wizard']['state']);
    }

    public function testCurrentStepIsSet(): void
    {
        $schema = $this->createSchema();
        $state = new WizardState('1.0');
        $state->setCurrentStep(1);

        $view = $this->createFormView($schema, $state);

        self::assertSame(1, $view->vars['wizard']['current_step']);
    }

    public function testIsFirstStep(): void
    {
        $schema = $this->createSchema();
        $state = new WizardState('1.0');

        $view = $this->createFormView($schema, $state);

        self::assertTrue($view->vars['wizard']['is_first_step']);
        self::assertFalse($view->vars['wizard']['is_last_step']);
    }

    public function testIsLastStep(): void
    {
        $schema = $this->createSchema();
        $state = new WizardState('1.0');
        $state->setCurrentStep(2);

        $view = $this->createFormView($schema, $state);

        self::assertFalse($view->vars['wizard']['is_first_step']);
        self::assertTrue($view->vars['wizard']['is_last_step']);
    }

    public function testStepsViewData(): void
    {
        $schema = $this->createSchema();
        $state = new WizardState('1.0');
        $state->setCurrentStep(1);
        $state->markStepCompleted('step1');
        $state->setStepErrors('step1', ['field' => ['Error message']]);

        $view = $this->createFormView($schema, $state);

        $steps = $view->vars['wizard']['steps'];

        self::assertCount(3, $steps);

        // First step
        self::assertSame('step1', $steps[0]['name']);
        self::assertSame('First Step', $steps[0]['label']);
        self::assertSame(0, $steps[0]['index']);
        self::assertFalse($steps[0]['is_current']);
        self::assertTrue($steps[0]['is_completed']);
        self::assertTrue($steps[0]['has_errors']);
        self::assertSame(['field' => ['Error message']], $steps[0]['errors']);
        self::assertTrue($steps[0]['can_navigate']);
        self::assertTrue($steps[0]['allow_back']);

        // Second step (current)
        self::assertSame('step2', $steps[1]['name']);
        self::assertSame('Second Step', $steps[1]['label']);
        self::assertTrue($steps[1]['is_current']);
        self::assertFalse($steps[1]['is_completed']);
        self::assertFalse($steps[1]['has_errors']);
        self::assertFalse($steps[1]['can_navigate']); // Cannot navigate to current step
        self::assertFalse($steps[1]['allow_back']);

        // Third step
        self::assertSame('step3', $steps[2]['name']);
        self::assertFalse($steps[2]['is_current']);
        self::assertFalse($steps[2]['is_completed']);
        self::assertFalse($steps[2]['can_navigate']); // Cannot navigate to future steps
    }

    public function testCanNavigateToCompletedSteps(): void
    {
        $schema = $this->createSchema();
        $state = new WizardState('1.0');
        $state->setCurrentStep(0);
        $state->markStepCompleted('step1'); // Must complete current step to navigate forward
        $state->markStepCompleted('step2'); // Mark a future step as completed

        $view = $this->createFormView($schema, $state);

        $steps = $view->vars['wizard']['steps'];

        // Step 2 should be navigable because current is completed AND step2 is completed
        self::assertTrue($steps[1]['can_navigate']);

        // Step 3 is not completed, so not navigable
        self::assertFalse($steps[2]['can_navigate']);
    }

    public function testWizardVarsNotAddedToChildForms(): void
    {
        $schema = $this->createSchema();
        $state = new WizardState('1.0');

        $form = $this->formFactory->createBuilder(FormType::class, null, [
            'wizard' => ['schema' => $schema, 'state' => $state],
        ])
            ->add('field', TextType::class)
            ->getForm();

        $view = $form->createView();

        // Root form should have wizard vars
        self::assertArrayHasKey('wizard', $view->vars);

        // Child form should not have wizard vars
        self::assertArrayNotHasKey('wizard', $view->children['field']->vars);
    }
}
