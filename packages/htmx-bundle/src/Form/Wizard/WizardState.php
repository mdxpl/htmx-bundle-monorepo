<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Wizard;

use ReflectionClass;

/**
 * Runtime state for a wizard form, tracking current step, data, and errors.
 */
final class WizardState
{
    private int $currentStep = 0;

    /** @var array<string, array<string, mixed>> */
    private array $stepData = [];

    /** @var array<string, array<string, string[]>> */
    private array $stepErrors = [];

    /** @var string[] */
    private array $completedSteps = [];

    public function __construct(
        private readonly string $schemaVersion,
    ) {
    }

    public function getSchemaVersion(): string
    {
        return $this->schemaVersion;
    }

    public function getCurrentStep(): int
    {
        return $this->currentStep;
    }

    public function setCurrentStep(int $step): void
    {
        $this->currentStep = max(0, $step);
    }

    /**
     * Get data for a specific step.
     *
     * @return array<string, mixed>
     */
    public function getStepData(string $stepName): array
    {
        return $this->stepData[$stepName] ?? [];
    }

    /**
     * Set data for a specific step.
     *
     * @param array<string, mixed> $data
     */
    public function setStepData(string $stepName, array $data): void
    {
        $this->stepData[$stepName] = $data;
    }

    /**
     * Get all step data.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllStepData(): array
    {
        return $this->stepData;
    }

    /**
     * Get all data keyed by step name.
     *
     * Returns the step data directly, where each key is a step name
     * and the value is the data for that step. This is suitable for
     * use with subforms where each step is a separate child form.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getAllData(): array
    {
        return $this->stepData;
    }

    /**
     * Get merged data from all steps (flat structure).
     *
     * Merges all step data into a single flat array. Useful for simple
     * forms without subforms, or for validation purposes.
     *
     * @return array<string, mixed>
     */
    public function getMergedData(): array
    {
        $merged = [];

        foreach ($this->stepData as $data) {
            $merged = array_merge($merged, $data);
        }

        return $merged;
    }

    /**
     * Get errors for a specific step.
     *
     * @return array<string, string[]>
     */
    public function getStepErrors(string $stepName): array
    {
        return $this->stepErrors[$stepName] ?? [];
    }

    /**
     * Set errors for a specific step.
     *
     * @param array<string, string[]> $errors Map of field names to error messages
     */
    public function setStepErrors(string $stepName, array $errors): void
    {
        if ($errors === []) {
            unset($this->stepErrors[$stepName]);
        } else {
            $this->stepErrors[$stepName] = $errors;
        }
    }

    /**
     * Clear errors for a specific step.
     */
    public function clearStepErrors(string $stepName): void
    {
        unset($this->stepErrors[$stepName]);
    }

    /**
     * Check if a step has errors.
     */
    public function hasStepErrors(string $stepName): bool
    {
        return isset($this->stepErrors[$stepName]) && $this->stepErrors[$stepName] !== [];
    }

    /**
     * Check if a step has been completed.
     */
    public function isStepCompleted(string $stepName): bool
    {
        return \in_array($stepName, $this->completedSteps, true);
    }

    /**
     * Mark a step as completed.
     */
    public function markStepCompleted(string $stepName): void
    {
        if (!\in_array($stepName, $this->completedSteps, true)) {
            $this->completedSteps[] = $stepName;
        }
    }

    /**
     * Unmark a step as completed.
     */
    public function unmarkStepCompleted(string $stepName): void
    {
        $this->completedSteps = array_values(
            array_filter($this->completedSteps, static fn (string $s) => $s !== $stepName),
        );
    }

    /**
     * Get all completed step names.
     *
     * @return string[]
     */
    public function getCompletedSteps(): array
    {
        return $this->completedSteps;
    }

    /**
     * Check if this is the first step of the given schema.
     */
    public function isFirstStep(WizardSchema $schema): bool
    {
        return $this->currentStep === 0;
    }

    /**
     * Check if this is the last step of the given schema.
     */
    public function isLastStep(WizardSchema $schema): bool
    {
        return $this->currentStep === $schema->getStepCount() - 1;
    }

    /**
     * Move to the next step.
     */
    public function nextStep(WizardSchema $schema): void
    {
        if ($this->currentStep < $schema->getStepCount() - 1) {
            $this->currentStep++;
        }
    }

    /**
     * Move to the previous step.
     */
    public function previousStep(): void
    {
        if ($this->currentStep > 0) {
            $this->currentStep--;
        }
    }

    /**
     * Reset the state to the beginning.
     */
    public function reset(): void
    {
        $this->currentStep = 0;
        $this->stepData = [];
        $this->stepErrors = [];
        $this->completedSteps = [];
    }

    /**
     * Create a new state with the given schema version, copying compatible data.
     *
     * @param string   $newVersion New schema version
     * @param string[] $validFields Fields to keep from the old state
     */
    public function copyForVersion(string $newVersion, array $validFields): self
    {
        $new = new self($newVersion);
        $new->currentStep = 0; // Reset to first step on migration

        $validFieldsSet = array_flip($validFields);

        foreach ($this->stepData as $stepName => $data) {
            $filteredData = array_intersect_key($data, $validFieldsSet);
            if ($filteredData !== []) {
                $new->stepData[$stepName] = $filteredData;
            }
        }

        return $new;
    }

    /**
     * Serialize state for storage.
     *
     * @return array<string, mixed>
     */
    public function __serialize(): array
    {
        return [
            'schemaVersion' => $this->schemaVersion,
            'currentStep' => $this->currentStep,
            'stepData' => $this->stepData,
            'stepErrors' => $this->stepErrors,
            'completedSteps' => $this->completedSteps,
        ];
    }

    /**
     * Unserialize state from storage.
     *
     * @param array{
     *     schemaVersion: string,
     *     currentStep: int,
     *     stepData: array<string, array<string, mixed>>,
     *     stepErrors?: array<string, array<string, string[]>>,
     *     completedSteps?: string[]
     * } $data
     */
    public function __unserialize(array $data): void
    {
        // Use reflection to set readonly property
        $reflection = new ReflectionClass($this);
        $property = $reflection->getProperty('schemaVersion');
        $property->setValue($this, $data['schemaVersion']);

        $this->currentStep = $data['currentStep'];
        $this->stepData = $data['stepData'];
        $this->stepErrors = $data['stepErrors'] ?? [];
        $this->completedSteps = $data['completedSteps'] ?? [];
    }
}
