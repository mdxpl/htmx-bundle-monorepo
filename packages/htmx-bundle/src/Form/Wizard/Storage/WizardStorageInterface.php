<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Wizard\Storage;

use Mdxpl\HtmxBundle\Form\Wizard\WizardState;

/**
 * Interface for storing and retrieving wizard state.
 */
interface WizardStorageInterface
{
    /**
     * Load wizard state from storage.
     *
     * @param string $wizardName Unique wizard identifier
     *
     * @return WizardState|null The stored state, or null if not found
     */
    public function load(string $wizardName): ?WizardState;

    /**
     * Save wizard state to storage.
     *
     * @param string      $wizardName Unique wizard identifier
     * @param WizardState $state      The state to save
     */
    public function save(string $wizardName, WizardState $state): void;

    /**
     * Clear wizard state from storage.
     *
     * @param string $wizardName Unique wizard identifier
     */
    public function clear(string $wizardName): void;
}
