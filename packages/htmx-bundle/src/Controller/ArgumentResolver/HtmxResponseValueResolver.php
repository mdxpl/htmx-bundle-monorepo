<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Controller\ArgumentResolver;

use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * It automatically resolves htmx request.
 * Use "HtmxRequest $htmx" as an argument in your controller.
 */
class HtmxResponseValueResolver implements ValueResolverInterface
{
    /**
     * @return HtmxRequest[]
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== HtmxRequest::class) {
            return [];
        }

        return [$request->attributes->get(HtmxRequest::REQUEST_ATTRIBUTE_NAME)];
    }
}
