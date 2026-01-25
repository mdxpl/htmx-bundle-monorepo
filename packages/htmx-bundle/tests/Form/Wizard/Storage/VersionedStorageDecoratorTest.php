<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Form\Wizard\Storage;

use Mdxpl\HtmxBundle\Form\Wizard\Migration\VersionMismatchStrategy;
use Mdxpl\HtmxBundle\Form\Wizard\Migration\WizardMigrationInterface;
use Mdxpl\HtmxBundle\Form\Wizard\Storage\VersionedStorageDecorator;
use Mdxpl\HtmxBundle\Form\Wizard\Storage\WizardStorageInterface;
use Mdxpl\HtmxBundle\Form\Wizard\WizardSchema;
use Mdxpl\HtmxBundle\Form\Wizard\WizardState;
use Mdxpl\HtmxBundle\Form\Wizard\WizardStep;
use PHPUnit\Framework\TestCase;

class VersionedStorageDecoratorTest extends TestCase
{
    private WizardStorageInterface $innerStorage;
    private VersionedStorageDecorator $decorator;

    protected function setUp(): void
    {
        $this->innerStorage = $this->createMock(WizardStorageInterface::class);
        $this->decorator = new VersionedStorageDecorator($this->innerStorage);
    }

    private function createSchema(
        string $version = '1.0',
        VersionMismatchStrategy $strategy = VersionMismatchStrategy::RESET,
        ?WizardMigrationInterface $migration = null,
    ): WizardSchema {
        return new WizardSchema(
            name: 'test',
            version: $version,
            steps: [
                new WizardStep(name: 'step1', label: 'Step 1', fields: ['email', 'password']),
                new WizardStep(name: 'step2', label: 'Step 2', fields: ['firstName', 'lastName']),
            ],
            mismatchStrategy: $strategy,
            migration: $migration,
        );
    }

    public function testLoadDelegatesToInner(): void
    {
        $state = new WizardState('1.0');

        $this->innerStorage
            ->method('load')
            ->with('test')
            ->willReturn($state);

        $result = $this->decorator->load('test');

        self::assertSame($state, $result);
    }

    public function testLoadReturnsNullWhenNotFound(): void
    {
        $this->innerStorage
            ->method('load')
            ->willReturn(null);

        $result = $this->decorator->load('test');

        self::assertNull($result);
    }

    public function testLoadWithSchemaReturnsStateWhenVersionMatches(): void
    {
        $state = new WizardState('1.0');
        $schema = $this->createSchema('1.0');

        $this->innerStorage
            ->method('load')
            ->with('test')
            ->willReturn($state);

        $result = $this->decorator->loadWithSchema('test', $schema);

        self::assertSame($state, $result);
    }

    public function testLoadWithSchemaReturnsNullWhenNotFound(): void
    {
        $schema = $this->createSchema('1.0');

        $this->innerStorage
            ->method('load')
            ->willReturn(null);

        $result = $this->decorator->loadWithSchema('test', $schema);

        self::assertNull($result);
    }

    public function testLoadWithSchemaResetsOnVersionMismatchWithResetStrategy(): void
    {
        $state = new WizardState('1.0');
        $schema = $this->createSchema('2.0', VersionMismatchStrategy::RESET);

        $this->innerStorage
            ->method('load')
            ->willReturn($state);

        $result = $this->decorator->loadWithSchema('test', $schema);

        self::assertNull($result);
    }

    public function testLoadWithSchemaKeepsValidFieldsOnVersionMismatchWithKeepStrategy(): void
    {
        $state = new WizardState('1.0');
        $state->setStepData('step1', ['email' => 'test@example.com', 'password' => 'secret', 'oldField' => 'value']);
        $state->setStepData('step2', ['firstName' => 'John', 'removedField' => 'gone']);

        $schema = $this->createSchema('2.0', VersionMismatchStrategy::KEEP);

        $this->innerStorage
            ->method('load')
            ->willReturn($state);

        $result = $this->decorator->loadWithSchema('test', $schema);

        self::assertNotNull($result);
        self::assertSame('2.0', $result->getSchemaVersion());

        // Should keep email, password (from step1 fields)
        $step1Data = $result->getStepData('step1');
        self::assertArrayHasKey('email', $step1Data);
        self::assertArrayHasKey('password', $step1Data);
        self::assertArrayNotHasKey('oldField', $step1Data);

        // Should keep firstName (from step2 fields)
        $step2Data = $result->getStepData('step2');
        self::assertArrayHasKey('firstName', $step2Data);
        self::assertArrayNotHasKey('removedField', $step2Data);
    }

    public function testLoadWithSchemaRunsMigrationOnVersionMismatchWithMigrateStrategy(): void
    {
        $oldState = new WizardState('1.0');
        $oldState->setStepData('step1', ['email' => 'test@example.com']);

        $migratedState = new WizardState('2.0');
        $migratedState->setStepData('step1', ['email' => 'migrated@example.com']);

        $migration = $this->createMock(WizardMigrationInterface::class);
        $migration
            ->method('migrate')
            ->willReturn($migratedState);

        $schema = $this->createSchema('2.0', VersionMismatchStrategy::MIGRATE, $migration);

        $this->innerStorage
            ->method('load')
            ->willReturn($oldState);

        $result = $this->decorator->loadWithSchema('test', $schema);

        self::assertSame($migratedState, $result);
    }

    public function testLoadWithSchemaMigrateFallsBackToKeepWhenNoMigrationDefined(): void
    {
        $state = new WizardState('1.0');
        $state->setStepData('step1', ['email' => 'test@example.com', 'oldField' => 'value']);

        $schema = $this->createSchema('2.0', VersionMismatchStrategy::MIGRATE);

        $this->innerStorage
            ->method('load')
            ->willReturn($state);

        $result = $this->decorator->loadWithSchema('test', $schema);

        self::assertNotNull($result);
        self::assertSame('2.0', $result->getSchemaVersion());

        $step1Data = $result->getStepData('step1');
        self::assertArrayHasKey('email', $step1Data);
        self::assertArrayNotHasKey('oldField', $step1Data);
    }

    public function testSaveDelegatesToInner(): void
    {
        $state = new WizardState('1.0');

        $this->innerStorage
            ->expects(self::once())
            ->method('save')
            ->with('test', $state);

        $this->decorator->save('test', $state);
    }

    public function testClearDelegatesToInner(): void
    {
        $this->innerStorage
            ->expects(self::once())
            ->method('clear')
            ->with('test');

        $this->decorator->clear('test');
    }
}
