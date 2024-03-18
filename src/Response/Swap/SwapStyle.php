<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Response\Swap;

/**
 * @link https://htmx.org/attributes/hx-swap/
 */
enum SwapStyle: string
{
    /**
     * Default value
     * Replace the inner HTML of the target element.
     */
    case INNER_HTML = 'innerHTML';

    /**
     * Replace the entire target element with the response.
     */
    case OUTER_HTML = 'outerHTML';

    /**
     * Insert the response before the target element.
     */
    case BEFORE_BEGIN = 'beforebegin';

    /**
     * Insert the response before the first child of the target element.
     */
    case AFTER_BEGIN = 'afterbegin';

    /**
     * Insert the response after the last child of the target element.
     */
    case BEFORE_END = 'beforeend';

    /**
     * Insert the response after the target element.
     */
    case AFTER_END = 'afterend';

    /**
     * Deletes the target element regardless of the response.
     */
    case DELETE = 'delete';

    /**
     * Does not append content from response (out of band items will still be processed).
     */
    case NONE = 'none';
}
