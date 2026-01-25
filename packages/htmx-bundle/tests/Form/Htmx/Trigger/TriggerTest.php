<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Form\Htmx\Trigger;

use Mdxpl\HtmxBundle\Form\Htmx\Trigger\Trigger;
use PHPUnit\Framework\TestCase;

class TriggerTest extends TestCase
{
    // ==========================================
    // Event Factories
    // ==========================================

    public function testEvent(): void
    {
        $trigger = Trigger::event('custom-event');

        self::assertSame('custom-event', (string) $trigger);
    }

    public function testClick(): void
    {
        $trigger = Trigger::click();

        self::assertSame('click', (string) $trigger);
    }

    public function testChange(): void
    {
        $trigger = Trigger::change();

        self::assertSame('change', (string) $trigger);
    }

    public function testSubmit(): void
    {
        $trigger = Trigger::submit();

        self::assertSame('submit', (string) $trigger);
    }

    public function testKeyup(): void
    {
        $trigger = Trigger::keyup();

        self::assertSame('keyup', (string) $trigger);
    }

    public function testKeydown(): void
    {
        $trigger = Trigger::keydown();

        self::assertSame('keydown', (string) $trigger);
    }

    public function testInput(): void
    {
        $trigger = Trigger::input();

        self::assertSame('input', (string) $trigger);
    }

    public function testFocus(): void
    {
        $trigger = Trigger::focus();

        self::assertSame('focus', (string) $trigger);
    }

    public function testBlur(): void
    {
        $trigger = Trigger::blur();

        self::assertSame('blur', (string) $trigger);
    }

    public function testMouseenter(): void
    {
        $trigger = Trigger::mouseenter();

        self::assertSame('mouseenter', (string) $trigger);
    }

    public function testMouseleave(): void
    {
        $trigger = Trigger::mouseleave();

        self::assertSame('mouseleave', (string) $trigger);
    }

    public function testLoad(): void
    {
        $trigger = Trigger::load();

        self::assertSame('load', (string) $trigger);
    }

    public function testRevealed(): void
    {
        $trigger = Trigger::revealed();

        self::assertSame('revealed', (string) $trigger);
    }

    public function testIntersect(): void
    {
        $trigger = Trigger::intersect();

        self::assertSame('intersect', (string) $trigger);
    }

    public function testEvery(): void
    {
        $trigger = Trigger::every(5000);

        self::assertSame('every 5000ms', (string) $trigger);
    }

    // ==========================================
    // Modifiers
    // ==========================================

    public function testChanged(): void
    {
        $trigger = Trigger::keyup()->changed();

        self::assertSame('keyup changed', (string) $trigger);
    }

    public function testDelay(): void
    {
        $trigger = Trigger::keyup()->delay(300);

        self::assertSame('keyup delay:300ms', (string) $trigger);
    }

    public function testThrottle(): void
    {
        $trigger = Trigger::keyup()->throttle(500);

        self::assertSame('keyup throttle:500ms', (string) $trigger);
    }

    public function testOnce(): void
    {
        $trigger = Trigger::click()->once();

        self::assertSame('click once', (string) $trigger);
    }

    public function testFrom(): void
    {
        $trigger = Trigger::click()->from('#other-element');

        self::assertSame('click from:#other-element', (string) $trigger);
    }

    public function testTarget(): void
    {
        $trigger = Trigger::click()->target('.button');

        self::assertSame('click target:.button', (string) $trigger);
    }

    public function testConsume(): void
    {
        $trigger = Trigger::click()->consume();

        self::assertSame('click consume', (string) $trigger);
    }

    public function testQueue(): void
    {
        $trigger = Trigger::click()->queue('last');

        self::assertSame('click queue:last', (string) $trigger);
    }

    public function testThreshold(): void
    {
        $trigger = Trigger::intersect()->threshold(0.5);

        self::assertSame('intersect threshold:0.5', (string) $trigger);
    }

    public function testRoot(): void
    {
        $trigger = Trigger::intersect()->root('#viewport');

        self::assertSame('intersect root:#viewport', (string) $trigger);
    }

    // ==========================================
    // Condition
    // ==========================================

    public function testCondition(): void
    {
        $trigger = Trigger::keyup()->condition('target.value.length >= 2');

        self::assertSame('keyup[target.value.length >= 2]', (string) $trigger);
    }

    public function testConditionWithKeyCode(): void
    {
        $trigger = Trigger::keydown()->condition('event.keyCode === 13');

        self::assertSame('keydown[event.keyCode === 13]', (string) $trigger);
    }

    // ==========================================
    // Combinations
    // ==========================================

    public function testChangedAndDelay(): void
    {
        $trigger = Trigger::keyup()->changed()->delay(300);

        self::assertSame('keyup changed delay:300ms', (string) $trigger);
    }

    public function testBlurChangedDelay(): void
    {
        $trigger = Trigger::blur()->changed()->delay(500);

        self::assertSame('blur changed delay:500ms', (string) $trigger);
    }

    public function testChangedDelayWithCondition(): void
    {
        $trigger = Trigger::keyup()
            ->changed()
            ->delay(300)
            ->condition('target.value.length >= 2');

        self::assertSame('keyup changed delay:300ms[target.value.length >= 2]', (string) $trigger);
    }

    public function testLoadWithDelay(): void
    {
        $trigger = Trigger::load()->delay(1000);

        self::assertSame('load delay:1000ms', (string) $trigger);
    }

    public function testIntersectWithThreshold(): void
    {
        $trigger = Trigger::intersect()->threshold(0.5);

        self::assertSame('intersect threshold:0.5', (string) $trigger);
    }

    public function testClickOnceWithFrom(): void
    {
        $trigger = Trigger::click()->once()->from('document');

        self::assertSame('click once from:document', (string) $trigger);
    }

    public function testComplexTrigger(): void
    {
        $trigger = Trigger::keyup()
            ->changed()
            ->delay(300)
            ->throttle(100)
            ->condition('target.value !== ""');

        self::assertSame('keyup changed delay:300ms throttle:100ms[target.value !== ""]', (string) $trigger);
    }

    // ==========================================
    // Fluent Interface
    // ==========================================

    public function testFluentInterfaceReturnsNewInstance(): void
    {
        $trigger1 = Trigger::click();
        $trigger2 = $trigger1->once();

        // Both should be the same instance due to fluent interface
        self::assertSame($trigger1, $trigger2);
    }

    public function testMultipleModifiersOrder(): void
    {
        $trigger = Trigger::keyup()
            ->changed()
            ->delay(300)
            ->once()
            ->consume();

        self::assertSame('keyup changed delay:300ms once consume', (string) $trigger);
    }
}
