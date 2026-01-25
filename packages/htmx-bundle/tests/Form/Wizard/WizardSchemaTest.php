<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Form\Wizard;

use InvalidArgumentException;
use Mdxpl\HtmxBundle\Form\Wizard\Migration\VersionMismatchStrategy;
use Mdxpl\HtmxBundle\Form\Wizard\Migration\WizardMigrationInterface;
use Mdxpl\HtmxBundle\Form\Wizard\WizardSchema;
use Mdxpl\HtmxBundle\Form\Wizard\WizardStep;
use PHPUnit\Framework\TestCase;

class WizardSchemaTest extends TestCase
{
    private function createTestSchema(): WizardSchema
    {
        return new WizardSchema(
            name: 'registration',
            version: '1.0',
            steps: [
                new WizardStep(
                    name: 'account',
                    label: 'Account',
                    fields: ['email', 'password'],
                    validationGroups: ['account_validation'],
                ),
                new WizardStep(
                    name: 'profile',
                    label: 'Profile',
                    fields: ['firstName', 'lastName'],
                    validationGroups: ['profile_validation'],
                ),
                new WizardStep(
                    name: 'preferences',
                    label: 'Preferences',
                    fields: ['newsletter', 'theme'],
                ),
            ],
        );
    }

    public function testGetName(): void
    {
        $schema = $this->createTestSchema();

        self::assertSame('registration', $schema->getName());
    }

    public function testGetVersion(): void
    {
        $schema = $this->createTestSchema();

        self::assertSame('1.0', $schema->getVersion());
    }

    public function testGetSteps(): void
    {
        $schema = $this->createTestSchema();

        self::assertCount(3, $schema->getSteps());
        self::assertSame('account', $schema->getSteps()[0]->name);
        self::assertSame('profile', $schema->getSteps()[1]->name);
        self::assertSame('preferences', $schema->getSteps()[2]->name);
    }

    public function testGetStepCount(): void
    {
        $schema = $this->createTestSchema();

        self::assertSame(3, $schema->getStepCount());
    }

    public function testGetStepByIndex(): void
    {
        $schema = $this->createTestSchema();

        self::assertSame('account', $schema->getStep(0)->name);
        self::assertSame('profile', $schema->getStep(1)->name);
        self::assertSame('preferences', $schema->getStep(2)->name);
    }

    public function testGetStepByName(): void
    {
        $schema = $this->createTestSchema();

        self::assertSame('Account', $schema->getStep('account')->label);
        self::assertSame('Profile', $schema->getStep('profile')->label);
        self::assertSame('Preferences', $schema->getStep('preferences')->label);
    }

    public function testGetStepByInvalidIndexThrowsException(): void
    {
        $schema = $this->createTestSchema();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Step with index "5" does not exist.');

        $schema->getStep(5);
    }

    public function testGetStepByInvalidNameThrowsException(): void
    {
        $schema = $this->createTestSchema();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Step "nonexistent" does not exist.');

        $schema->getStep('nonexistent');
    }

    public function testGetStepIndex(): void
    {
        $schema = $this->createTestSchema();

        self::assertSame(0, $schema->getStepIndex('account'));
        self::assertSame(1, $schema->getStepIndex('profile'));
        self::assertSame(2, $schema->getStepIndex('preferences'));
    }

    public function testHasStep(): void
    {
        $schema = $this->createTestSchema();

        self::assertTrue($schema->hasStep('account'));
        self::assertTrue($schema->hasStep('profile'));
        self::assertFalse($schema->hasStep('nonexistent'));
    }

    public function testGetValidationGroups(): void
    {
        $schema = $this->createTestSchema();

        // Step with specific validation groups - returns only those groups
        $groups = $schema->getValidationGroups('account');
        self::assertContains('account_validation', $groups);
        self::assertNotContains('Default', $groups);
        self::assertCount(1, $groups);

        // Step without validation groups - falls back to Default
        $groups = $schema->getValidationGroups('preferences');
        self::assertContains('Default', $groups);
        self::assertCount(1, $groups);
    }

    public function testGetAllValidationGroups(): void
    {
        $schema = $this->createTestSchema();

        // Returns only step-specific groups (no Default when steps have groups)
        $groups = $schema->getAllValidationGroups();
        self::assertContains('account_validation', $groups);
        self::assertContains('profile_validation', $groups);
        self::assertNotContains('Default', $groups);
    }

    public function testGetAllValidationGroupsFallsBackToDefault(): void
    {
        // Schema with no step-specific validation groups
        $schema = new WizardSchema(
            name: 'test',
            version: '1.0',
            steps: [
                new WizardStep(name: 'step1', label: 'Step 1'),
                new WizardStep(name: 'step2', label: 'Step 2'),
            ],
        );

        $groups = $schema->getAllValidationGroups();
        self::assertSame(['Default'], $groups);
    }

    public function testGetAllFields(): void
    {
        $schema = $this->createTestSchema();

        $fields = $schema->getAllFields();
        self::assertContains('email', $fields);
        self::assertContains('password', $fields);
        self::assertContains('firstName', $fields);
        self::assertContains('lastName', $fields);
        self::assertContains('newsletter', $fields);
        self::assertContains('theme', $fields);
    }

    public function testDefaultMismatchStrategy(): void
    {
        $schema = new WizardSchema(
            name: 'test',
            version: '1.0',
            steps: [new WizardStep(name: 'step1', label: 'Step 1')],
        );

        self::assertSame(VersionMismatchStrategy::RESET, $schema->getMismatchStrategy());
    }

    public function testCustomMismatchStrategy(): void
    {
        $schema = new WizardSchema(
            name: 'test',
            version: '1.0',
            steps: [new WizardStep(name: 'step1', label: 'Step 1')],
            mismatchStrategy: VersionMismatchStrategy::KEEP,
        );

        self::assertSame(VersionMismatchStrategy::KEEP, $schema->getMismatchStrategy());
    }

    public function testMigrationIsNull(): void
    {
        $schema = $this->createTestSchema();

        self::assertNull($schema->getMigration());
    }

    public function testMigrationCanBeSet(): void
    {
        $migration = $this->createMock(WizardMigrationInterface::class);

        $schema = new WizardSchema(
            name: 'test',
            version: '1.0',
            steps: [new WizardStep(name: 'step1', label: 'Step 1')],
            mismatchStrategy: VersionMismatchStrategy::MIGRATE,
            migration: $migration,
        );

        self::assertSame($migration, $schema->getMigration());
    }
}
