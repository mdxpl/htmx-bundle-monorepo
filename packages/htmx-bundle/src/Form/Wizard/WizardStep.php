<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Wizard;

/**
 * Configuration for a single step in a wizard form.
 */
final readonly class WizardStep
{
    /**
     * @param string   $name             Step identifier (used internally)
     * @param string   $label            Display label shown in tabs
     * @param bool     $allowBack        Whether navigation back from this step is allowed
     * @param string[] $validationGroups Validation groups to apply for this step
     * @param string[] $fields           Field names belonging to this step (used for migration)
     */
    public function __construct(
        public string $name,
        public string $label,
        public bool $allowBack = true,
        public array $validationGroups = [],
        public array $fields = [],
    ) {
    }
}
