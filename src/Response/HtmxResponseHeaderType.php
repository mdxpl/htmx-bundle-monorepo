<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response;

/**
 * @link https://htmx.org/reference/#response_headers
 */
enum HtmxResponseHeaderType: string
{
    /**
     * Allows you to do a client-side redirect that does not do a full page reload.
     */
    case LOCATION = 'HX-Location';

    /**
     * Pushes a new URL into the history stack.
     */
    case PUSH_URL = 'HX-Push-Url';

    /**
     * Can be used to do a client-side redirect to a new location.
     */
    case REDIRECT = 'HX-Redirect';

    /**
     * If set to “true,” the client-side will do a full refresh of the page.
     */
    case REFRESH = 'HX-Refresh';

    /**
     * Replaces the current URL in the location bar.
     */
    case REPLACE_URL = 'HX-Replace-Url';

    /**
     * Allows you to specify how the response will be swapped. See hx-swap for possible values.
     */
    case RESWAP = 'HX-Reswap';

    /**
     * A CSS selector that updates the target of the content update to a different element on the page.
     */
    case RETARGET = 'HX-Retarget';

    /**
     * A CSS selector that allows you to choose which part of the response is used to be swapped in. Overrides an existing hx-select on the triggering element.
     */
    case RESELECT = 'HX-Reselect';

    /**
     * Allows you to trigger client-side events.
     */
    case TRIGGER = 'HX-Trigger';

    /**
     * Allows you to trigger client-side events after the settle step.
     */
    case TRIGGER_AFTER_SETTLE = 'HX-Trigger-After-Settle';

    /**
     * Allows you to trigger client-side events after the swap step.
     */
    case TRIGGER_AFTER_SWAP = 'HX-Trigger-After-Swap';
}
