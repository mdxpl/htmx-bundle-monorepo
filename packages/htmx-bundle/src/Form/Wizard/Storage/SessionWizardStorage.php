<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Wizard\Storage;

use Mdxpl\HtmxBundle\Form\Wizard\WizardState;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Session-based storage for wizard state.
 */
final class SessionWizardStorage implements WizardStorageInterface
{
    private const SESSION_PREFIX = 'wizard_';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function load(string $wizardName): ?WizardState
    {
        $session = $this->requestStack->getSession();
        $key = $this->getSessionKey($wizardName);

        $state = $session->get($key);

        if ($state instanceof WizardState) {
            return $state;
        }

        return null;
    }

    public function save(string $wizardName, WizardState $state): void
    {
        $session = $this->requestStack->getSession();
        $key = $this->getSessionKey($wizardName);

        $session->set($key, $state);
    }

    public function clear(string $wizardName): void
    {
        $session = $this->requestStack->getSession();
        $key = $this->getSessionKey($wizardName);

        $session->remove($key);
    }

    private function getSessionKey(string $wizardName): string
    {
        return self::SESSION_PREFIX . $wizardName;
    }
}
