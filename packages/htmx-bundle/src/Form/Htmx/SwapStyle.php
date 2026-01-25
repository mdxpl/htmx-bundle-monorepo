<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Form\Htmx;

/**
 * Swap styles for hx-swap attribute.
 *
 * @link https://htmx.org/attributes/hx-swap/
 */
enum SwapStyle: string
{
    /**
     * Replace the inner html of the target element.
     */
    case InnerHTML = 'innerHTML';

    /**
     * Replace the entire target element with the response.
     */
    case OuterHTML = 'outerHTML';

    /**
     * Insert the response before the target element.
     */
    case BeforeBegin = 'beforebegin';

    /**
     * Insert the response before the first child of the target element.
     */
    case AfterBegin = 'afterbegin';

    /**
     * Insert the response after the last child of the target element.
     */
    case BeforeEnd = 'beforeend';

    /**
     * Insert the response after the target element.
     */
    case AfterEnd = 'afterend';

    /**
     * Deletes the target element regardless of the response.
     */
    case Delete = 'delete';

    /**
     * Does not append content from response (out of band items will still be processed).
     */
    case None = 'none';
}
