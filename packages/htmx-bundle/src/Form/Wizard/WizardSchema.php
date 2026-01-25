<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Wizard;

use InvalidArgumentException;
use Mdxpl\HtmxBundle\Form\Wizard\Migration\VersionMismatchStrategy;
use Mdxpl\HtmxBundle\Form\Wizard\Migration\WizardMigrationInterface;

/**
 * Defines the schema for a multi-step wizard form.
 */
final class WizardSchema
{
    /** @var array<int, WizardStep> */
    private readonly array $steps;

    /** @var array<string, int> */
    private readonly array $stepIndexMap;

    /**
     * @param string                        $name               Unique wizard identifier
     * @param string                        $version            Schema version (e.g., '1.0', '2.0')
     * @param WizardStep[]                  $steps              Array of wizard steps
     * @param NavigationStrategy            $navigationStrategy Strategy for navigating between steps
     * @param VersionMismatchStrategy       $mismatchStrategy   Strategy for handling version mismatches
     * @param WizardMigrationInterface|null $migration          Optional custom migration logic
     */
    public function __construct(
        private readonly string $name,
        private readonly string $version,
        array $steps,
        private readonly NavigationStrategy $navigationStrategy = NavigationStrategy::LINEAR,
        private readonly VersionMismatchStrategy $mismatchStrategy = VersionMismatchStrategy::RESET,
        private readonly ?WizardMigrationInterface $migration = null,
    ) {
        $this->steps = array_values($steps);

        $indexMap = [];
        foreach ($this->steps as $index => $step) {
            $indexMap[$step->name] = $index;
        }
        $this->stepIndexMap = $indexMap;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return WizardStep[]
     */
    public function getSteps(): array
    {
        return $this->steps;
    }

    public function getStepCount(): int
    {
        return \count($this->steps);
    }

    /**
     * Get a step by its index or name.
     *
     * @throws InvalidArgumentException If step not found
     */
    public function getStep(int|string $indexOrName): WizardStep
    {
        if (\is_string($indexOrName)) {
            $index = $this->getStepIndex($indexOrName);
        } else {
            $index = $indexOrName;
        }

        if (!isset($this->steps[$index])) {
            throw new InvalidArgumentException(\sprintf('Step with index "%d" does not exist.', $index));
        }

        return $this->steps[$index];
    }

    /**
     * Get the index of a step by its name.
     *
     * @throws InvalidArgumentException If step not found
     */
    public function getStepIndex(string $name): int
    {
        if (!isset($this->stepIndexMap[$name])) {
            throw new InvalidArgumentException(\sprintf('Step "%s" does not exist.', $name));
        }

        return $this->stepIndexMap[$name];
    }

    /**
     * Check if a step with the given name exists.
     */
    public function hasStep(string $name): bool
    {
        return isset($this->stepIndexMap[$name]);
    }

    /**
     * Get validation groups for a specific step.
     *
     * @return string[]
     */
    public function getValidationGroups(string $stepName): array
    {
        $step = $this->getStep($stepName);

        if ($step->validationGroups !== []) {
            return $step->validationGroups;
        }

        // Fallback to Default only if no step-specific groups defined
        return ['Default'];
    }

    /**
     * Get all validation groups from all steps.
     *
     * @return string[]
     */
    public function getAllValidationGroups(): array
    {
        $groups = [];

        foreach ($this->steps as $step) {
            if ($step->validationGroups !== []) {
                $groups = array_merge($groups, $step->validationGroups);
            }
        }

        $unique = array_unique($groups);

        return $unique !== [] ? $unique : ['Default'];
    }

    /**
     * Get all field names from all steps.
     *
     * @return string[]
     */
    public function getAllFields(): array
    {
        $fields = [];

        foreach ($this->steps as $step) {
            $fields = array_merge($fields, $step->fields);
        }

        return array_unique($fields);
    }

    public function getNavigationStrategy(): NavigationStrategy
    {
        return $this->navigationStrategy;
    }

    public function getMismatchStrategy(): VersionMismatchStrategy
    {
        return $this->mismatchStrategy;
    }

    public function getMigration(): ?WizardMigrationInterface
    {
        return $this->migration;
    }

    /**
     * Check if navigation to a specific step is allowed.
     *
     * Navigation rules:
     * - FREE: No restrictions, can go anywhere
     * - SEQUENTIAL: Only adjacent steps (prev/next), forward requires current completed
     * - COMPLETED_ONLY: Only completed steps or back, forward requires current completed
     * - LINEAR: Back freely, forward to next or completed steps, forward requires current completed
     */
    public function canNavigateToStep(int $targetStep, int $currentStep, WizardState $state): bool
    {
        // Boundary checks
        if ($targetStep < 0 || $targetStep >= $this->getStepCount()) {
            return false;
        }

        if ($targetStep === $currentStep) {
            return false; // Already on this step
        }

        // FREE strategy: no restrictions
        if ($this->navigationStrategy === NavigationStrategy::FREE) {
            return true;
        }

        $isForward = $targetStep > $currentStep;
        $currentStepDef = $this->getStep($currentStep);
        $targetStepDef = $this->getStep($targetStep);

        // For all non-FREE strategies: forward navigation requires current step to be completed
        if ($isForward && !$state->isStepCompleted($currentStepDef->name)) {
            return false;
        }

        // Strategy-specific rules for SEQUENTIAL and LINEAR
        // (FREE already handled above with early return)
        if ($this->navigationStrategy === NavigationStrategy::SEQUENTIAL) {
            // SEQUENTIAL: only one step at a time (prev or next)
            return abs($targetStep - $currentStep) === 1;
        }

        // LINEAR: back freely, forward to next or completed steps
        return !$isForward || $targetStep === $currentStep + 1 || $state->isStepCompleted($targetStepDef->name);
    }
}
