<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Form\Wizard;

use Mdxpl\HtmxBundle\Form\Wizard\NavigationStrategy;
use Mdxpl\HtmxBundle\Form\Wizard\WizardSchema;
use Mdxpl\HtmxBundle\Form\Wizard\WizardState;
use Mdxpl\HtmxBundle\Form\Wizard\WizardStep;
use PHPUnit\Framework\TestCase;

class NavigationStrategyTest extends TestCase
{
    private function createSchema(NavigationStrategy $strategy): WizardSchema
    {
        return new WizardSchema(
            name: 'test',
            version: '1.0',
            steps: [
                new WizardStep(name: 'step1', label: 'Step 1'),
                new WizardStep(name: 'step2', label: 'Step 2'),
                new WizardStep(name: 'step3', label: 'Step 3'),
                new WizardStep(name: 'step4', label: 'Step 4'),
            ],
            navigationStrategy: $strategy,
        );
    }

    // ==========================================
    // FREE Strategy
    // ==========================================

    public function testFreeStrategyAllowsNavigationToAnyStep(): void
    {
        $schema = $this->createSchema(NavigationStrategy::FREE);
        $state = new WizardState('1.0');
        $state->setCurrentStep(0);

        // Can navigate to any step
        self::assertTrue($schema->canNavigateToStep(1, 0, $state));
        self::assertTrue($schema->canNavigateToStep(2, 0, $state));
        self::assertTrue($schema->canNavigateToStep(3, 0, $state));
    }

    public function testFreeStrategyCannotNavigateToCurrentStep(): void
    {
        $schema = $this->createSchema(NavigationStrategy::FREE);
        $state = new WizardState('1.0');
        $state->setCurrentStep(1);

        self::assertFalse($schema->canNavigateToStep(1, 1, $state));
    }

    // ==========================================
    // SEQUENTIAL Strategy
    // ==========================================

    public function testSequentialStrategyAllowsOnlyAdjacentSteps(): void
    {
        $schema = $this->createSchema(NavigationStrategy::SEQUENTIAL);
        $state = new WizardState('1.0');
        $state->setCurrentStep(1);
        $state->markStepCompleted('step2'); // Must complete current step to go forward

        // Can navigate to adjacent steps
        self::assertTrue($schema->canNavigateToStep(0, 1, $state)); // Previous (always allowed)
        self::assertTrue($schema->canNavigateToStep(2, 1, $state)); // Next (current completed)

        // Cannot skip steps even with current completed
        self::assertFalse($schema->canNavigateToStep(3, 1, $state));
    }

    public function testSequentialStrategyFromFirstStep(): void
    {
        $schema = $this->createSchema(NavigationStrategy::SEQUENTIAL);
        $state = new WizardState('1.0');
        $state->setCurrentStep(0);
        $state->markStepCompleted('step1'); // Must complete current step to go forward

        // Can only go to next step
        self::assertTrue($schema->canNavigateToStep(1, 0, $state));
        self::assertFalse($schema->canNavigateToStep(2, 0, $state));
    }

    public function testSequentialStrategyBlocksForwardWithoutCompletion(): void
    {
        $schema = $this->createSchema(NavigationStrategy::SEQUENTIAL);
        $state = new WizardState('1.0');
        $state->setCurrentStep(0);
        // Current step NOT completed

        // Cannot go forward without completing current
        self::assertFalse($schema->canNavigateToStep(1, 0, $state));
    }

    // ==========================================
    // LINEAR Strategy (default)
    // ==========================================

    public function testLinearStrategyAllowsBackNavigation(): void
    {
        $schema = $this->createSchema(NavigationStrategy::LINEAR);
        $state = new WizardState('1.0');
        $state->setCurrentStep(2);

        // Can go back to any previous step
        self::assertTrue($schema->canNavigateToStep(0, 2, $state));
        self::assertTrue($schema->canNavigateToStep(1, 2, $state));
    }

    public function testLinearStrategyBlocksForwardNavigation(): void
    {
        $schema = $this->createSchema(NavigationStrategy::LINEAR);
        $state = new WizardState('1.0');
        $state->setCurrentStep(1);

        // Cannot skip forward
        self::assertFalse($schema->canNavigateToStep(3, 1, $state));
    }

    public function testLinearStrategyAllowsCompletedFutureSteps(): void
    {
        $schema = $this->createSchema(NavigationStrategy::LINEAR);
        $state = new WizardState('1.0');
        $state->setCurrentStep(0);
        $state->markStepCompleted('step1'); // Must complete current step first
        $state->markStepCompleted('step3');

        // Can navigate to completed future step
        self::assertTrue($schema->canNavigateToStep(2, 0, $state));

        // Cannot navigate to uncompleted future step (step4)
        self::assertFalse($schema->canNavigateToStep(3, 0, $state));
    }

    public function testLinearStrategyBlocksForwardWithoutCurrentCompletion(): void
    {
        $schema = $this->createSchema(NavigationStrategy::LINEAR);
        $state = new WizardState('1.0');
        $state->setCurrentStep(0);
        // Current step NOT completed

        // Cannot go forward without completing current
        self::assertFalse($schema->canNavigateToStep(1, 0, $state));
    }

    public function testLinearStrategyAllowsNextStepWhenCurrentCompleted(): void
    {
        $schema = $this->createSchema(NavigationStrategy::LINEAR);
        $state = new WizardState('1.0');
        $state->setCurrentStep(0);
        $state->markStepCompleted('step1'); // Complete current step

        // Can go to next step
        self::assertTrue($schema->canNavigateToStep(1, 0, $state));
    }

    // ==========================================
    // Edge Cases
    // ==========================================

    public function testCannotNavigateToInvalidStep(): void
    {
        $schema = $this->createSchema(NavigationStrategy::FREE);
        $state = new WizardState('1.0');

        self::assertFalse($schema->canNavigateToStep(-1, 0, $state));
        self::assertFalse($schema->canNavigateToStep(10, 0, $state));
    }

    public function testGetNavigationStrategy(): void
    {
        $schema = $this->createSchema(NavigationStrategy::SEQUENTIAL);

        self::assertSame(NavigationStrategy::SEQUENTIAL, $schema->getNavigationStrategy());
    }

    public function testDefaultNavigationStrategyIsLinear(): void
    {
        $schema = new WizardSchema(
            name: 'test',
            version: '1.0',
            steps: [new WizardStep(name: 'step1', label: 'Step 1')],
        );

        self::assertSame(NavigationStrategy::LINEAR, $schema->getNavigationStrategy());
    }

    // ==========================================
    // Labels and Descriptions
    // ==========================================

    public function testGetLabel(): void
    {
        self::assertSame('Free', NavigationStrategy::FREE->getLabel());
        self::assertSame('Sequential', NavigationStrategy::SEQUENTIAL->getLabel());
        self::assertSame('Linear', NavigationStrategy::LINEAR->getLabel());
    }

    public function testGetDescription(): void
    {
        self::assertSame('Jump anywhere without validation', NavigationStrategy::FREE->getDescription());
        self::assertSame('One step at a time (prev/next only)', NavigationStrategy::SEQUENTIAL->getDescription());
        self::assertSame('Back freely, forward after completing', NavigationStrategy::LINEAR->getDescription());
    }
}
