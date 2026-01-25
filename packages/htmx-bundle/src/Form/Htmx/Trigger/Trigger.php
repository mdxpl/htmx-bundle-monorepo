<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Htmx\Trigger;

use Stringable;

/**
 * Builder for htmx trigger specifications.
 *
 * Usage:
 * ```php
 * Trigger::click()                           // 'click'
 * Trigger::keyup()->changed()->delay(300)    // 'keyup changed delay:300ms'
 * Trigger::blur()->changed()->delay(500)     // 'blur changed delay:500ms'
 * Trigger::event('custom-event')->once()     // 'custom-event once'
 * Trigger::load()->delay(1000)               // 'load delay:1000ms'
 * Trigger::intersect()->threshold(0.5)       // 'intersect threshold:0.5'
 * Trigger::keyup()->condition('target.value.length >= 2')  // 'keyup[target.value.length >= 2]'
 * ```
 *
 * @link https://htmx.org/attributes/hx-trigger/
 */
final class Trigger implements Stringable
{
    private string $event;

    /** @var list<string> */
    private array $modifiers = [];

    private ?string $condition = null;

    private function __construct(string $event)
    {
        $this->event = $event;
    }

    // ==========================================
    // Event Factories
    // ==========================================

    /**
     * Creates a trigger for a custom event.
     */
    public static function event(string $event): self
    {
        return new self($event);
    }

    /**
     * Trigger on click event.
     */
    public static function click(): self
    {
        return new self('click');
    }

    /**
     * Trigger on change event.
     */
    public static function change(): self
    {
        return new self('change');
    }

    /**
     * Trigger on submit event.
     */
    public static function submit(): self
    {
        return new self('submit');
    }

    /**
     * Trigger on keyup event.
     */
    public static function keyup(): self
    {
        return new self('keyup');
    }

    /**
     * Trigger on keydown event.
     */
    public static function keydown(): self
    {
        return new self('keydown');
    }

    /**
     * Trigger on input event.
     */
    public static function input(): self
    {
        return new self('input');
    }

    /**
     * Trigger on focus event.
     */
    public static function focus(): self
    {
        return new self('focus');
    }

    /**
     * Trigger on blur event.
     */
    public static function blur(): self
    {
        return new self('blur');
    }

    /**
     * Trigger on mouseenter event.
     */
    public static function mouseenter(): self
    {
        return new self('mouseenter');
    }

    /**
     * Trigger on mouseleave event.
     */
    public static function mouseleave(): self
    {
        return new self('mouseleave');
    }

    /**
     * Trigger on page load.
     */
    public static function load(): self
    {
        return new self('load');
    }

    /**
     * Trigger when element is revealed (scrolled into viewport).
     */
    public static function revealed(): self
    {
        return new self('revealed');
    }

    /**
     * Trigger when element intersects viewport.
     */
    public static function intersect(): self
    {
        return new self('intersect');
    }

    /**
     * Trigger every N milliseconds.
     */
    public static function every(int $milliseconds): self
    {
        return new self("every {$milliseconds}ms");
    }

    // ==========================================
    // Modifiers
    // ==========================================

    /**
     * Only trigger if the value has changed.
     */
    public function changed(): self
    {
        $this->modifiers[] = 'changed';

        return $this;
    }

    /**
     * Add a delay before triggering (debounce).
     *
     * @param int $milliseconds Delay in milliseconds
     */
    public function delay(int $milliseconds): self
    {
        $this->modifiers[] = "delay:{$milliseconds}ms";

        return $this;
    }

    /**
     * Throttle the trigger to once per interval.
     *
     * @param int $milliseconds Throttle interval in milliseconds
     */
    public function throttle(int $milliseconds): self
    {
        $this->modifiers[] = "throttle:{$milliseconds}ms";

        return $this;
    }

    /**
     * Only trigger once.
     */
    public function once(): self
    {
        $this->modifiers[] = 'once';

        return $this;
    }

    /**
     * Listen for events from other elements (useful with `from:` modifier).
     *
     * @param string $selector CSS selector for the element to listen on
     */
    public function from(string $selector): self
    {
        $this->modifiers[] = "from:{$selector}";

        return $this;
    }

    /**
     * Target a different element for the trigger.
     *
     * @param string $selector CSS selector for target
     */
    public function target(string $selector): self
    {
        $this->modifiers[] = "target:{$selector}";

        return $this;
    }

    /**
     * Consume the event (prevent bubbling).
     */
    public function consume(): self
    {
        $this->modifiers[] = 'consume';

        return $this;
    }

    /**
     * Queue events that occur during a request.
     *
     * @param string $strategy Queue strategy: 'first', 'last', 'all', 'none'
     */
    public function queue(string $strategy): self
    {
        $this->modifiers[] = "queue:{$strategy}";

        return $this;
    }

    /**
     * Set intersection threshold (for intersect trigger).
     *
     * @param float $threshold Value between 0 and 1
     */
    public function threshold(float $threshold): self
    {
        $this->modifiers[] = "threshold:{$threshold}";

        return $this;
    }

    /**
     * Set root margin for intersection (for intersect trigger).
     */
    public function root(string $selector): self
    {
        $this->modifiers[] = "root:{$selector}";

        return $this;
    }

    // ==========================================
    // Condition
    // ==========================================

    /**
     * Add a JavaScript condition that must be true for trigger to fire.
     *
     * @param string $expression JavaScript expression
     *
     * @example ->condition('target.value.length >= 2')
     * @example ->condition('event.keyCode === 13')
     */
    public function condition(string $expression): self
    {
        $this->condition = $expression;

        return $this;
    }

    // ==========================================
    // Output
    // ==========================================

    public function __toString(): string
    {
        $parts = [$this->event];

        if ($this->modifiers !== []) {
            $parts[] = implode(' ', $this->modifiers);
        }

        $result = implode(' ', $parts);

        if ($this->condition !== null) {
            $result .= '[' . $this->condition . ']';
        }

        return $result;
    }
}
