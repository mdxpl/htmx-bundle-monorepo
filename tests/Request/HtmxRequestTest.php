<?php

declare(strict_types=1);

namespace Mdxpl\HtmxBundle\Tests\Request;

use Mdxpl\HtmxBundle\Request\HtmxRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class HtmxRequestTest extends TestCase
{

    public function testCreateFromSymfonyHttpRequest(): void
    {
        $request = Request::create('https://example2.com');
        $request->headers->add(
            [
                'HX-Request' => 'true',
                'HX-Boosted' => 'true',
                'HX-History-Restore-Request' => 'true',
                'HX-Current-URL' => 'https://example.com',
                'HX-Prompt' => 'Yes',
                'HX-Target' => '#element',
                'HX-Trigger-Name' => 'elementName',
                'HX-Trigger' => '#anAnotherElement',
            ],
        );

        $htmxRequest = HtmxRequest::createFromSymfonyHttpRequest($request);

        $this->assertTrue($htmxRequest->isHtmx);
        $this->assertTrue($htmxRequest->isBoosted);
        $this->assertTrue($htmxRequest->isForHistoryRestoration);
        $this->assertEquals('https://example.com', $htmxRequest->currentUrl);
        $this->assertEquals('Yes', $htmxRequest->prompt);
        $this->assertEquals('#element', $htmxRequest->target);
        $this->assertEquals('elementName', $htmxRequest->triggerName);
        $this->assertEquals('#anAnotherElement', $htmxRequest->trigger);
    }
}
