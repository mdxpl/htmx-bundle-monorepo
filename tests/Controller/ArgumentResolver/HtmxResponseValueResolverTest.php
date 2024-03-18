<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Controller\ArgumentResolver;

use Mdxpl\HtmxBundle\Controller\ArgumentResolver\HtmxResponseValueResolver;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class HtmxResponseValueResolverTest extends TestCase
{
    public function testSupportsHtmxRequest(): void
    {
        $resolver = new HtmxResponseValueResolver();
        $request = Request::create('/');
        $request->headers->set('HX-Request', 'true');
        $request->attributes->set('htmxRequest', HtmxRequest::createFromSymfonyHttpRequest($request));

        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn(HtmxRequest::class);

        $resolved = iterator_to_array($resolver->resolve($request, $argument));

        $this->assertCount(
            1,
            $resolved,
            'Expected exactly one resolved argument.',
        );

        $this->assertInstanceOf(
            HtmxRequest::class,
            $resolved[0],
            'The resolved argument should be an instance of HtmxRequest.',
        );
    }

    public function testDoesNotSupportOtherRequestTypes(): void
    {
        $resolver = new HtmxResponseValueResolver();
        $request = Request::create('/');
        $argument = $this->createMock(ArgumentMetadata::class);
        $argument->method('getType')->willReturn('SomeOtherRequestType');

        $resolved = iterator_to_array($resolver->resolve($request, $argument));

        $this->assertEmpty($resolved, 'Expected no resolved arguments for non-HtmxRequest types.');
    }
}
