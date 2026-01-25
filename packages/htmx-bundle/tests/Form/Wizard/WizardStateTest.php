<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Form\Wizard;

use Mdxpl\HtmxBundle\Form\Wizard\WizardSchema;
use Mdxpl\HtmxBundle\Form\Wizard\WizardState;
use Mdxpl\HtmxBundle\Form\Wizard\WizardStep;
use PHPUnit\Framework\TestCase;

class WizardStateTest extends TestCase
{
    private function createTestSchema(): WizardSchema
    {
        return new WizardSchema(
            name: 'test',
            version: '1.0',
            steps: [
                new WizardStep(name: 'step1', label: 'Step 1'),
                new WizardStep(name: 'step2', label: 'Step 2'),
                new WizardStep(name: 'step3', label: 'Step 3'),
            ],
        );
    }

    public function testInitialState(): void
    {
        $state = new WizardState('1.0');

        self::assertSame('1.0', $state->getSchemaVersion());
        self::assertSame(0, $state->getCurrentStep());
        self::assertSame([], $state->getAllData());
        self::assertSame([], $state->getCompletedSteps());
    }

    public function testSetCurrentStep(): void
    {
        $state = new WizardState('1.0');

        $state->setCurrentStep(2);
        self::assertSame(2, $state->getCurrentStep());

        // Negative values should be clamped to 0
        $state->setCurrentStep(-5);
        self::assertSame(0, $state->getCurrentStep());
    }

    public function testStepData(): void
    {
        $state = new WizardState('1.0');

        $state->setStepData('step1', ['email' => 'test@example.com']);
        $state->setStepData('step2', ['firstName' => 'John', 'lastName' => 'Doe']);

        self::assertSame(['email' => 'test@example.com'], $state->getStepData('step1'));
        self::assertSame(['firstName' => 'John', 'lastName' => 'Doe'], $state->getStepData('step2'));
        self::assertSame([], $state->getStepData('step3')); // Non-existent returns empty array
    }

    public function testGetAllStepData(): void
    {
        $state = new WizardState('1.0');

        $state->setStepData('step1', ['email' => 'test@example.com']);
        $state->setStepData('step2', ['firstName' => 'John']);

        $allStepData = $state->getAllStepData();

        self::assertArrayHasKey('step1', $allStepData);
        self::assertArrayHasKey('step2', $allStepData);
        self::assertSame(['email' => 'test@example.com'], $allStepData['step1']);
    }

    public function testGetAllDataReturnsNestedStructure(): void
    {
        $state = new WizardState('1.0');

        $state->setStepData('step1', ['email' => 'test@example.com', 'password' => 'secret']);
        $state->setStepData('step2', ['firstName' => 'John', 'lastName' => 'Doe']);

        $allData = $state->getAllData();

        // getAllData returns nested structure keyed by step name
        self::assertArrayHasKey('step1', $allData);
        self::assertArrayHasKey('step2', $allData);
        self::assertSame('test@example.com', $allData['step1']['email']);
        self::assertSame('secret', $allData['step1']['password']);
        self::assertSame('John', $allData['step2']['firstName']);
        self::assertSame('Doe', $allData['step2']['lastName']);
    }

    public function testGetMergedDataFlattensSteps(): void
    {
        $state = new WizardState('1.0');

        $state->setStepData('step1', ['email' => 'test@example.com', 'password' => 'secret']);
        $state->setStepData('step2', ['firstName' => 'John', 'lastName' => 'Doe']);

        $mergedData = $state->getMergedData();

        // getMergedData returns flat structure
        self::assertSame('test@example.com', $mergedData['email']);
        self::assertSame('secret', $mergedData['password']);
        self::assertSame('John', $mergedData['firstName']);
        self::assertSame('Doe', $mergedData['lastName']);
    }

    public function testStepErrors(): void
    {
        $state = new WizardState('1.0');

        $errors = ['email' => ['Email is required', 'Invalid format']];
        $state->setStepErrors('step1', $errors);

        self::assertTrue($state->hasStepErrors('step1'));
        self::assertFalse($state->hasStepErrors('step2'));
        self::assertSame($errors, $state->getStepErrors('step1'));
        self::assertSame([], $state->getStepErrors('step2'));
    }

    public function testClearStepErrors(): void
    {
        $state = new WizardState('1.0');

        $state->setStepErrors('step1', ['email' => ['Error']]);
        self::assertTrue($state->hasStepErrors('step1'));

        $state->clearStepErrors('step1');
        self::assertFalse($state->hasStepErrors('step1'));
    }

    public function testEmptyErrorsRemovesEntry(): void
    {
        $state = new WizardState('1.0');

        $state->setStepErrors('step1', ['email' => ['Error']]);
        self::assertTrue($state->hasStepErrors('step1'));

        $state->setStepErrors('step1', []);
        self::assertFalse($state->hasStepErrors('step1'));
    }

    public function testCompletedSteps(): void
    {
        $state = new WizardState('1.0');

        self::assertFalse($state->isStepCompleted('step1'));

        $state->markStepCompleted('step1');
        self::assertTrue($state->isStepCompleted('step1'));
        self::assertFalse($state->isStepCompleted('step2'));

        $state->markStepCompleted('step2');
        self::assertSame(['step1', 'step2'], $state->getCompletedSteps());
    }

    public function testMarkStepCompletedIsIdempotent(): void
    {
        $state = new WizardState('1.0');

        $state->markStepCompleted('step1');
        $state->markStepCompleted('step1');
        $state->markStepCompleted('step1');

        self::assertCount(1, $state->getCompletedSteps());
    }

    public function testUnmarkStepCompleted(): void
    {
        $state = new WizardState('1.0');

        $state->markStepCompleted('step1');
        $state->markStepCompleted('step2');
        self::assertTrue($state->isStepCompleted('step1'));

        $state->unmarkStepCompleted('step1');
        self::assertFalse($state->isStepCompleted('step1'));
        self::assertTrue($state->isStepCompleted('step2'));
    }

    public function testIsFirstStep(): void
    {
        $state = new WizardState('1.0');
        $schema = $this->createTestSchema();

        self::assertTrue($state->isFirstStep($schema));

        $state->setCurrentStep(1);
        self::assertFalse($state->isFirstStep($schema));
    }

    public function testIsLastStep(): void
    {
        $state = new WizardState('1.0');
        $schema = $this->createTestSchema();

        self::assertFalse($state->isLastStep($schema));

        $state->setCurrentStep(2);
        self::assertTrue($state->isLastStep($schema));
    }

    public function testNextStep(): void
    {
        $state = new WizardState('1.0');
        $schema = $this->createTestSchema();

        self::assertSame(0, $state->getCurrentStep());

        $state->nextStep($schema);
        self::assertSame(1, $state->getCurrentStep());

        $state->nextStep($schema);
        self::assertSame(2, $state->getCurrentStep());

        // Should not go beyond last step
        $state->nextStep($schema);
        self::assertSame(2, $state->getCurrentStep());
    }

    public function testPreviousStep(): void
    {
        $state = new WizardState('1.0');
        $state->setCurrentStep(2);

        $state->previousStep();
        self::assertSame(1, $state->getCurrentStep());

        $state->previousStep();
        self::assertSame(0, $state->getCurrentStep());

        // Should not go below 0
        $state->previousStep();
        self::assertSame(0, $state->getCurrentStep());
    }

    public function testReset(): void
    {
        $state = new WizardState('1.0');

        $state->setCurrentStep(2);
        $state->setStepData('step1', ['email' => 'test@example.com']);
        $state->setStepErrors('step1', ['email' => ['Error']]);
        $state->markStepCompleted('step1');

        $state->reset();

        self::assertSame(0, $state->getCurrentStep());
        self::assertSame([], $state->getAllData());
        self::assertFalse($state->hasStepErrors('step1'));
        self::assertSame([], $state->getCompletedSteps());
    }

    public function testCopyForVersion(): void
    {
        $state = new WizardState('1.0');
        $state->setCurrentStep(2);
        $state->setStepData('step1', ['email' => 'test@example.com', 'password' => 'secret']);
        $state->setStepData('step2', ['firstName' => 'John', 'lastName' => 'Doe', 'age' => 30]);
        $state->markStepCompleted('step1');

        // Only keep email and firstName
        $newState = $state->copyForVersion('2.0', ['email', 'firstName']);

        self::assertSame('2.0', $newState->getSchemaVersion());
        self::assertSame(0, $newState->getCurrentStep()); // Reset to 0

        // Check filtered data
        $step1Data = $newState->getStepData('step1');
        self::assertArrayHasKey('email', $step1Data);
        self::assertArrayNotHasKey('password', $step1Data);

        $step2Data = $newState->getStepData('step2');
        self::assertArrayHasKey('firstName', $step2Data);
        self::assertArrayNotHasKey('lastName', $step2Data);
        self::assertArrayNotHasKey('age', $step2Data);
    }

    public function testSerialization(): void
    {
        $state = new WizardState('1.0');
        $state->setCurrentStep(1);
        $state->setStepData('step1', ['email' => 'test@example.com']);
        $state->setStepErrors('step1', ['email' => ['Error']]);
        $state->markStepCompleted('step1');

        $serialized = serialize($state);
        $unserialized = unserialize($serialized);

        self::assertInstanceOf(WizardState::class, $unserialized);
        self::assertSame('1.0', $unserialized->getSchemaVersion());
        self::assertSame(1, $unserialized->getCurrentStep());
        self::assertSame(['email' => 'test@example.com'], $unserialized->getStepData('step1'));
        self::assertTrue($unserialized->hasStepErrors('step1'));
        self::assertTrue($unserialized->isStepCompleted('step1'));
    }
}
