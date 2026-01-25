<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Wizard\Storage;

use Mdxpl\HtmxBundle\Form\Wizard\Migration\VersionMismatchStrategy;
use Mdxpl\HtmxBundle\Form\Wizard\WizardSchema;
use Mdxpl\HtmxBundle\Form\Wizard\WizardState;

/**
 * Decorator that handles schema version mismatches when loading wizard state.
 *
 * This decorator wraps another storage implementation and applies migration
 * strategies when the stored state version doesn't match the current schema version.
 */
final readonly class VersionedStorageDecorator implements WizardStorageInterface
{
    public function __construct(
        private WizardStorageInterface $inner,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * Note: This method loads raw state. Use loadWithSchema() for version-aware loading.
     */
    public function load(string $wizardName): ?WizardState
    {
        return $this->inner->load($wizardName);
    }

    /**
     * Load wizard state with schema version checking and migration.
     *
     * @param string       $wizardName The wizard name
     * @param WizardSchema $schema     The current schema to validate against
     *
     * @return WizardState|null The state (possibly migrated), or null if reset/not found
     */
    public function loadWithSchema(string $wizardName, WizardSchema $schema): ?WizardState
    {
        $state = $this->inner->load($wizardName);

        if ($state === null) {
            return null;
        }

        if ($state->getSchemaVersion() === $schema->getVersion()) {
            return $state; // No migration needed
        }

        return match ($schema->getMismatchStrategy()) {
            VersionMismatchStrategy::RESET => null, // Start fresh
            VersionMismatchStrategy::MIGRATE => $this->migrate($state, $schema),
            VersionMismatchStrategy::KEEP => $this->filterToCurrentFields($state, $schema),
        };
    }

    public function save(string $wizardName, WizardState $state): void
    {
        $this->inner->save($wizardName, $state);
    }

    public function clear(string $wizardName): void
    {
        $this->inner->clear($wizardName);
    }

    /**
     * Run custom migration logic.
     */
    private function migrate(WizardState $oldState, WizardSchema $schema): WizardState
    {
        $migration = $schema->getMigration();

        if ($migration === null) {
            // No migration defined, fall back to KEEP behavior
            return $this->filterToCurrentFields($oldState, $schema);
        }

        return $migration->migrate($oldState, $schema);
    }

    /**
     * Keep only fields that exist in the current schema.
     */
    private function filterToCurrentFields(WizardState $oldState, WizardSchema $schema): WizardState
    {
        $validFields = $schema->getAllFields();

        return $oldState->copyForVersion($schema->getVersion(), $validFields);
    }
}
