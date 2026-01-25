<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Wizard;

/**
 * Strategy for navigating between wizard steps.
 */
enum NavigationStrategy: string
{
    /**
     * Free navigation - can go to any step at any time without validation.
     */
    case FREE = 'free';

    /**
     * Sequential navigation - can only go to next or previous step.
     * Forward requires completing current step first.
     */
    case SEQUENTIAL = 'sequential';

    /**
     * Linear navigation - can go back freely, but forward only after completing current step.
     * Can also jump to previously completed steps. Default behavior.
     */
    case LINEAR = 'linear';

    /**
     * Get human-readable label for the strategy.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::FREE => 'Free',
            self::SEQUENTIAL => 'Sequential',
            self::LINEAR => 'Linear',
        };
    }

    /**
     * Get description explaining what the strategy does.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::FREE => 'Jump anywhere without validation',
            self::SEQUENTIAL => 'One step at a time (prev/next only)',
            self::LINEAR => 'Back freely, forward after completing',
        };
    }
}
