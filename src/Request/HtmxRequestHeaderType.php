<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Request;

/**
 * @link https://htmx.org/reference/#request_headers
 */
enum HtmxRequestHeaderType: string
{
    /**
     * Indicates that the request is via an element using hx-boost.
     */
    case BOOSTED = 'HX-Boosted';

    /**
     * The current URL of the browser.
     */
    case CURRENT_URL = 'HX-Current-URL';

    /**
     * “true” if the request is for history restoration after a miss in the local history cache.
     */
    case HISTORY_RESTORE_REQUEST = 'HX-History-Restore-Request';

    /**
     * The user response to an hx-prompt.
     */
    case PROMPT = 'HX-Prompt';

    /**
     * Always “true”.
     */
    case REQUEST = 'HX-Request';

    /**
     * The id of the target element if it exists.
     */
    case TARGET = 'HX-Target';

    /**
     * The name of the triggered element if it exists.
     */
    case TRIGGER_NAME = 'HX-Trigger-Name';

    /**
     * The id of the triggered element if it exists.
     */
    case TRIGGER = 'HX-Trigger';
}
