<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Form\Wizard;

use Mdxpl\HtmxBundle\Form\Wizard\WizardStep;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class WizardStepTest extends TestCase
{
    public function testConstructWithDefaults(): void
    {
        $step = new WizardStep(
            name: 'account',
            label: 'Account',
        );

        self::assertSame('account', $step->name);
        self::assertSame('Account', $step->label);
        self::assertTrue($step->allowBack);
        self::assertSame([], $step->validationGroups);
        self::assertSame([], $step->fields);
    }

    public function testConstructWithAllOptions(): void
    {
        $step = new WizardStep(
            name: 'payment',
            label: 'Payment Info',
            allowBack: false,
            validationGroups: ['payment_validation'],
            fields: ['cardNumber', 'expiryDate', 'cvv'],
        );

        self::assertSame('payment', $step->name);
        self::assertSame('Payment Info', $step->label);
        self::assertFalse($step->allowBack);
        self::assertSame(['payment_validation'], $step->validationGroups);
        self::assertSame(['cardNumber', 'expiryDate', 'cvv'], $step->fields);
    }

    public function testStepIsReadonly(): void
    {
        $step = new WizardStep(
            name: 'test',
            label: 'Test',
        );

        // Verify readonly by checking class reflection
        $reflection = new ReflectionClass($step);
        self::assertTrue($reflection->isReadOnly());
    }
}
