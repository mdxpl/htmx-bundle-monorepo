<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Wizard;

use Mdxpl\HtmxBundle\Form\Wizard\Migration\VersionMismatchStrategy;
use Mdxpl\HtmxBundle\Form\Wizard\Storage\WizardStorageInterface;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Helper service for managing wizard forms in controllers.
 *
 * Provides methods for loading/saving wizard state, navigating between steps,
 * validating data, and building htmx responses.
 *
 * @example
 *     #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
 *     public function register(HtmxRequest $htmx, Request $request): HtmxResponse
 *     {
 *         $schema = RegistrationWizardSchema::create();
 *         $state = $this->wizard->loadOrStart($schema);
 *
 *         $form = $this->createForm(RegistrationFormType::class, $state->getAllData(), [
 *             'wizard' => ['schema' => $schema, 'state' => $state],
 *         ]);
 *
 *         $form->handleRequest($request);
 *
 *         if ($form->isSubmitted() && $form->isValid()) {
 *             $this->wizard->saveStepData($schema, $state, $form->getData());
 *             $this->wizard->nextStep($schema, $state);
 *         }
 *
 *         return $this->wizard->buildStepResponse($htmx, $schema, $state, 'wizard.html.twig');
 *     }
 */
final class WizardHelper
{
    public function __construct(
        private readonly WizardStorageInterface $storage,
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * Start a new wizard from the beginning.
     */
    public function start(WizardSchema $schema): WizardState
    {
        $state = new WizardState($schema->getVersion());
        $this->storage->save($schema->getName(), $state);

        return $state;
    }

    /**
     * Load existing wizard state or start a new one.
     *
     * Handles schema version mismatches according to the schema's migration strategy.
     */
    public function loadOrStart(WizardSchema $schema): WizardState
    {
        $state = $this->storage->load($schema->getName());

        if ($state === null) {
            return $this->start($schema);
        }

        // Check version mismatch
        if ($state->getSchemaVersion() !== $schema->getVersion()) {
            $state = $this->handleVersionMismatch($state, $schema);
            if ($state !== null) {
                $this->storage->save($schema->getName(), $state);
            } else {
                return $this->start($schema);
            }
        }

        return $state;
    }

    /**
     * Save the current state to storage.
     */
    public function save(WizardSchema $schema, WizardState $state): void
    {
        $this->storage->save($schema->getName(), $state);
    }

    /**
     * Save step data and mark the step as having data.
     *
     * Supports both flat form data and subform data (nested by step name).
     * If the data contains a key matching the step name, that subform data is extracted.
     * Otherwise, fields are filtered based on the step's field configuration.
     *
     * @param array<string, mixed> $data The form data for the current step
     */
    public function saveStepData(WizardSchema $schema, WizardState $state, array $data): void
    {
        $currentStep = $schema->getStep($state->getCurrentStep());
        $stepName = $currentStep->name;

        // Check if data is structured with subforms (keyed by step name)
        if (isset($data[$stepName]) && \is_array($data[$stepName])) {
            // Extract the subform data for this step
            /** @var array<string, mixed> $stepData */
            $stepData = $data[$stepName];
        } elseif ($currentStep->fields !== []) {
            // Filter flat data to only include fields for this step
            $stepData = array_intersect_key($data, array_flip($currentStep->fields));
        } else {
            $stepData = $data;
        }

        $state->setStepData($stepName, $stepData);
        $this->storage->save($schema->getName(), $state);
    }

    /**
     * Mark the current step as completed.
     */
    public function markStepCompleted(WizardSchema $schema, WizardState $state): void
    {
        $currentStep = $schema->getStep($state->getCurrentStep());
        $state->markStepCompleted($currentStep->name);
        $state->clearStepErrors($currentStep->name);
        $this->storage->save($schema->getName(), $state);
    }

    /**
     * Validate data for the current step.
     *
     * @param object|array<string, mixed> $data The data to validate
     */
    public function validateStep(
        WizardSchema $schema,
        WizardState $state,
        object|array $data,
    ): ConstraintViolationListInterface {
        $currentStep = $schema->getStep($state->getCurrentStep());
        $groups = $schema->getValidationGroups($currentStep->name);

        return $this->validator->validate($data, null, $groups);
    }

    /**
     * Validate all step data.
     *
     * @param object $object The object to validate with all data
     *
     * @return array<string, ConstraintViolationListInterface> Map of step names to violations
     */
    public function validateAll(WizardSchema $schema, object $object): array
    {
        $allErrors = [];

        foreach ($schema->getSteps() as $step) {
            $groups = $schema->getValidationGroups($step->name);
            $violations = $this->validator->validate($object, null, $groups);

            if ($violations->count() > 0) {
                $allErrors[$step->name] = $violations;
            }
        }

        return $allErrors;
    }

    /**
     * Validate all steps using array data and store errors in state.
     *
     * @param array<string, mixed> $allData All form data
     *
     * @return array<string, array<string, string[]>> Map of step names to field errors
     */
    public function validateAllSteps(WizardSchema $schema, WizardState $state, array $allData): array
    {
        $allErrors = [];

        foreach ($schema->getSteps() as $step) {
            $groups = $schema->getValidationGroups($step->name);

            // Skip steps with only 'Default' group (like confirmation step)
            if ($groups === ['Default']) {
                continue;
            }

            $violations = $this->validator->validate($allData, null, $groups);

            if ($violations->count() > 0) {
                $stepErrors = [];
                foreach ($violations as $violation) {
                    $path = $violation->getPropertyPath();
                    $field = trim($path, '[]');
                    if (!isset($stepErrors[$field])) {
                        $stepErrors[$field] = [];
                    }
                    $stepErrors[$field][] = (string) $violation->getMessage();
                }
                $allErrors[$step->name] = $stepErrors;
                $state->setStepErrors($step->name, $stepErrors);
            } else {
                $state->clearStepErrors($step->name);
            }
        }

        $this->storage->save($schema->getName(), $state);

        return $allErrors;
    }

    /**
     * Get the first step with errors.
     */
    public function getFirstStepWithErrors(WizardSchema $schema, WizardState $state): ?int
    {
        foreach ($schema->getSteps() as $index => $step) {
            if ($state->hasStepErrors($step->name)) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Extract errors from a submitted form and store them in the state.
     *
     * @param FormInterface<mixed> $form
     *
     * @return array<string, string[]> Map of field names to error messages
     */
    public function extractFormErrors(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors(true) as $error) {
            $origin = $error->getOrigin();
            $fieldName = $origin !== null ? $origin->getName() : '_form';

            if (!isset($errors[$fieldName])) {
                $errors[$fieldName] = [];
            }

            $errors[$fieldName][] = $error->getMessage();
        }

        return $errors;
    }

    /**
     * Store form errors in the state for the current step.
     *
     * @param FormInterface<mixed> $form
     */
    public function setStepErrors(WizardSchema $schema, WizardState $state, FormInterface $form): void
    {
        $currentStep = $schema->getStep($state->getCurrentStep());
        $errors = $this->extractFormErrors($form);
        $state->setStepErrors($currentStep->name, $errors);
        $this->storage->save($schema->getName(), $state);
    }

    /**
     * Move to the next step.
     */
    public function nextStep(WizardSchema $schema, WizardState $state): void
    {
        $state->nextStep($schema);
        $this->storage->save($schema->getName(), $state);
    }

    /**
     * Move to the previous step.
     *
     * Respects the allowBack property on the current step.
     *
     * @return bool True if navigation was successful, false if blocked
     */
    public function previousStep(WizardSchema $schema, WizardState $state): bool
    {
        $currentStepIndex = $state->getCurrentStep();

        // Cannot go back from first step
        if ($currentStepIndex === 0) {
            return false;
        }

        // Check if current step allows going back
        $currentStep = $schema->getStep($currentStepIndex);
        if (!$currentStep->allowBack) {
            return false;
        }

        $state->previousStep();
        $this->storage->save($schema->getName(), $state);

        return true;
    }

    /**
     * Go to a specific step.
     *
     * Respects the navigation strategy defined in the schema.
     *
     * @return bool True if navigation was successful, false if blocked by strategy
     */
    public function goToStep(WizardSchema $schema, WizardState $state, int $step): bool
    {
        $currentStep = $state->getCurrentStep();

        if (!$schema->canNavigateToStep($step, $currentStep, $state)) {
            return false;
        }

        $state->setCurrentStep($step);
        $this->storage->save($schema->getName(), $state);

        return true;
    }

    /**
     * Clear wizard state and start fresh.
     */
    public function clear(WizardSchema $schema): void
    {
        $this->storage->clear($schema->getName());
    }

    /**
     * Check if the wizard is on the first step.
     */
    public function isFirstStep(WizardState $state): bool
    {
        return $state->getCurrentStep() === 0;
    }

    /**
     * Check if the wizard is on the last step.
     */
    public function isLastStep(WizardSchema $schema, WizardState $state): bool
    {
        return $state->isLastStep($schema);
    }

    /**
     * Build an htmx response for the current wizard step.
     *
     * @param array<string, mixed> $extraViewData Additional data to pass to the template
     */
    public function buildStepResponse(
        HtmxRequest $htmx,
        WizardSchema $schema,
        WizardState $state,
        string $template,
        array $extraViewData = [],
    ): HtmxResponse {
        $currentStep = $schema->getStep($state->getCurrentStep());

        $viewData = array_merge([
            'wizard_schema' => $schema,
            'wizard_state' => $state,
            'wizard_current_step' => $currentStep,
        ], $extraViewData);

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->view($template, $viewData)
            ->build();
    }

    /**
     * Handle version mismatch between stored state and current schema.
     */
    private function handleVersionMismatch(WizardState $state, WizardSchema $schema): ?WizardState
    {
        return match ($schema->getMismatchStrategy()) {
            VersionMismatchStrategy::RESET => null,
            VersionMismatchStrategy::MIGRATE => $this->migrateState($state, $schema),
            VersionMismatchStrategy::KEEP => $this->filterToCurrentFields($state, $schema),
        };
    }

    /**
     * Run custom migration logic.
     */
    private function migrateState(WizardState $oldState, WizardSchema $schema): WizardState
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
