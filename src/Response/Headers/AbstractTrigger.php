<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Headers;

/**
 * If you wish to invoke multiple events, you can simply add additional properties to top level array, like so:
 * ["event1"=>"A message", "event2"=>"Another message"]
 *
 * You may also trigger multiple events with no additional details by sending event names separated by commas, like so:
 * event1, event2
 *
 * @link https://htmx.org/headers/hx-trigger/
 */
abstract class AbstractTrigger implements HtmxResponseHeader
{
    public function __construct(private readonly array|string $events)
    {
    }

    public function getValue(): string
    {
        if (\is_string($this->events)) {
            return $this->events;
        }

        return json_encode($this->events, JSON_THROW_ON_ERROR);
    }
}
