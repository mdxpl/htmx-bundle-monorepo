<?php

declare(strict_types=1);

namespace App\Form\Wizard;

use Mdxpl\HtmxBundle\Form\Wizard\Migration\VersionMismatchStrategy;
use Mdxpl\HtmxBundle\Form\Wizard\NavigationStrategy;
use Mdxpl\HtmxBundle\Form\Wizard\WizardSchema;
use Mdxpl\HtmxBundle\Form\Wizard\WizardStep;

/**
 * Wizard schema for the multi-step registration demo.
 */
final class RegistrationWizardSchema
{
    public static function create(NavigationStrategy $navigationStrategy = NavigationStrategy::LINEAR): WizardSchema
    {
        return new WizardSchema(
            name: 'registration',
            version: '1.0',
            steps: [
                new WizardStep(name: 'account', label: 'Account'),
                new WizardStep(name: 'profile', label: 'Profile'),
                new WizardStep(name: 'preferences', label: 'Preferences'),
                new WizardStep(name: 'confirmation', label: 'Confirm'),
            ],
            navigationStrategy: $navigationStrategy,
            mismatchStrategy: VersionMismatchStrategy::KEEP,
        );
    }
}
