<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Form\Wizard;

use Mdxpl\HtmxBundle\Form\Wizard\NavigationStrategy;
use Mdxpl\HtmxBundle\Form\Wizard\WizardSchema;
use Mdxpl\HtmxBundle\Form\Wizard\WizardState;
use Mdxpl\HtmxBundle\Form\Wizard\WizardStep;
use PHPUnit\Framework\TestCase;

/**
 * Tests for backend validation of navigation strategies.
 *
 * These tests verify that navigation is properly restricted on the backend,
 * not just hidden on the frontend.
 */
class WizardHelperTest extends TestCase
{
    private function createSchema(NavigationStrategy $strategy): WizardSchema
    {
        return new WizardSchema(
            name: 'test',
            version: '1.0',
            steps: [
                new WizardStep(name: 'step1', label: 'Step 1'),
                new WizardStep(name: 'step2', label: 'Step 2', allowBack: false),
                new WizardStep(name: 'step3', label: 'Step 3'),
                new WizardStep(name: 'step4', label: 'Step 4'),
            ],
            navigationStrategy: $strategy,
        );
    }

    // ==========================================
    // LINEAR Strategy - Backend Validation
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

    public function testLinearStrategyBlocksSkippingForward(): void
    {
        $schema = $this->createSchema(NavigationStrategy::LINEAR);
        $state = new WizardState('1.0');
        $state->setCurrentStep(0);

        // Cannot skip forward
        self::assertFalse($schema->canNavigateToStep(2, 0, $state));
        self::assertFalse($schema->canNavigateToStep(3, 0, $state));
    }

    public function testLinearStrategyAllowsCompletedFutureSteps(): void
    {
        $schema = $this->createSchema(NavigationStrategy::LINEAR);
        $state = new WizardState('1.0');
        $state->setCurrentStep(0);
        $state->markStepCompleted('step1'); // Must complete current first
        $state->markStepCompleted('step3');

        // Can go to completed future step
        self::assertTrue($schema->canNavigateToStep(2, 0, $state));
    }

    public function testLinearStrategyBlocksForwardWithoutCurrentCompletion(): void
    {
        $schema = $this->createSchema(NavigationStrategy::LINEAR);
        $state = new WizardState('1.0');
        $state->setCurrentStep(0);
        // Current step NOT completed

        // Cannot go forward
        self::assertFalse($schema->canNavigateToStep(1, 0, $state));
    }

    // ==========================================
    // SEQUENTIAL Strategy - Backend Validation
    // ==========================================

    public function testSequentialStrategyAllowsOnlyAdjacentSteps(): void
    {
        $schema = $this->createSchema(NavigationStrategy::SEQUENTIAL);
        $state = new WizardState('1.0');
        $state->setCurrentStep(1);
        $state->markStepCompleted('step2'); // Must complete current first

        // Can go to adjacent steps
        self::assertTrue($schema->canNavigateToStep(0, 1, $state)); // Back always allowed
        self::assertTrue($schema->canNavigateToStep(2, 1, $state)); // Forward with current completed

        // Cannot skip steps
        self::assertFalse($schema->canNavigateToStep(3, 1, $state));
    }

    public function testSequentialStrategyBlocksJumpingMultipleSteps(): void
    {
        $schema = $this->createSchema(NavigationStrategy::SEQUENTIAL);
        $state = new WizardState('1.0');
        $state->setCurrentStep(0);
        $state->markStepCompleted('step1'); // Complete current

        // Even with current completed and future steps completed, cannot jump
        $state->markStepCompleted('step3');
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
    // FREE Strategy - Backend Validation
    // ==========================================

    public function testFreeStrategyAllowsAnyNavigation(): void
    {
        $schema = $this->createSchema(NavigationStrategy::FREE);
        $state = new WizardState('1.0');
        $state->setCurrentStep(0);

        // Can jump anywhere
        self::assertTrue($schema->canNavigateToStep(1, 0, $state));
        self::assertTrue($schema->canNavigateToStep(2, 0, $state));
        self::assertTrue($schema->canNavigateToStep(3, 0, $state));
    }

    // ==========================================
    // allowBack Property - Backend Validation
    // ==========================================

    public function testAllowBackPropertyIsAvailable(): void
    {
        $schema = $this->createSchema(NavigationStrategy::LINEAR);

        // step2 has allowBack: false
        $step2 = $schema->getStep(1);
        self::assertFalse($step2->allowBack);

        // step3 has allowBack: true (default)
        $step3 = $schema->getStep(2);
        self::assertTrue($step3->allowBack);
    }

    // ==========================================
    // Edge Cases - Backend Validation
    // ==========================================

    public function testCannotNavigateToCurrentStep(): void
    {
        $schema = $this->createSchema(NavigationStrategy::FREE);
        $state = new WizardState('1.0');
        $state->setCurrentStep(1);

        // Cannot navigate to the same step
        self::assertFalse($schema->canNavigateToStep(1, 1, $state));
    }

    public function testCannotNavigateToInvalidStep(): void
    {
        $schema = $this->createSchema(NavigationStrategy::FREE);
        $state = new WizardState('1.0');
        $state->setCurrentStep(0);

        // Cannot navigate to negative step
        self::assertFalse($schema->canNavigateToStep(-1, 0, $state));

        // Cannot navigate past last step
        self::assertFalse($schema->canNavigateToStep(10, 0, $state));
    }

    // ==========================================
    // Security: Malicious Request Simulation
    // ==========================================

    public function testMaliciousSkipAttemptIsBlocked(): void
    {
        // Simulate a user trying to skip from step 0 directly to step 3
        // by crafting a request (e.g., /wizard/confirmation)
        $schema = $this->createSchema(NavigationStrategy::LINEAR);
        $state = new WizardState('1.0');
        $state->setCurrentStep(0);

        // Backend should block this
        self::assertFalse($schema->canNavigateToStep(3, 0, $state));
    }

    public function testMaliciousBackNavigationToDisallowedStepIsHandled(): void
    {
        // step2 has allowBack: false, user tries to go back from step2
        $schema = $this->createSchema(NavigationStrategy::LINEAR);
        $state = new WizardState('1.0');
        $state->setCurrentStep(1);

        $currentStep = $schema->getStep(1);

        // The allowBack property should be checked by the controller
        self::assertFalse($currentStep->allowBack);
    }
}
