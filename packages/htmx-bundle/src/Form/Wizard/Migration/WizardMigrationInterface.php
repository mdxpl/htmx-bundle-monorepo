<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Wizard\Migration;

use Mdxpl\HtmxBundle\Form\Wizard\WizardSchema;
use Mdxpl\HtmxBundle\Form\Wizard\WizardState;

/**
 * Interface for migrating wizard state between schema versions.
 */
interface WizardMigrationInterface
{
    /**
     * Migrate state from an old schema version to the current schema.
     *
     * @param WizardState  $oldState  The state from the previous schema version
     * @param WizardSchema $newSchema The current schema to migrate to
     *
     * @return WizardState The migrated state compatible with the new schema
     */
    public function migrate(WizardState $oldState, WizardSchema $newSchema): WizardState;
}
