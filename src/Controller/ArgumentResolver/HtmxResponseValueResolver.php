<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Controller\ArgumentResolver;

use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * It automatically resolves htmx response, just use "HtmxRequest $request" as an argument in your controller.
 */
class HtmxResponseValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();
        if (!$argumentType || $argumentType !== HtmxRequest::class) {
            return [];
        }

        return [HtmxRequest::createFromSymfonyHttpRequest($request)];
    }
}