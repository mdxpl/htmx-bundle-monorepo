<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Wizard\Migration;

/**
 * Strategy for handling version mismatches between stored wizard state and current schema.
 */
enum VersionMismatchStrategy: string
{
    /**
     * Clear storage and start fresh. Use for major breaking changes.
     */
    case RESET = 'reset';

    /**
     * Run custom migration logic. Use for complex data transformations.
     */
    case MIGRATE = 'migrate';

    /**
     * Keep only fields that exist in the current schema. Use when adding optional fields.
     */
    case KEEP = 'keep';
}
