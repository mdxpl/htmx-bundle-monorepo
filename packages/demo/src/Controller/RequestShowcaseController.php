<?php

declare(strict_types=1);

namespace App\Controller;

use Mdxpl\HtmxBundle\Attribute\HtmxOnly;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/request-showcase')]
final class RequestShowcaseController extends AbstractController
{
    #[Route('', name: 'app_request_showcase')]
    public function index(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->view('request_showcase.html.twig', [
                'htmx' => $htmx,
            ])
            ->build();
    }

    /**
     * Endpoint for testing HtmxRequest properties
     */
    #[Route('/request', name: 'app_request_showcase_request')]
    #[HtmxOnly]
    public function request(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('request_showcase.html.twig', 'actionResult', ['htmx' => $htmx])
            ->viewBlock('request_showcase.html.twig', 'requestInfoOob', ['htmx' => $htmx])
            ->build();
    }

    /**
     * Endpoint that triggers a prompt dialog - demonstrates prompt property
     */
    #[Route('/prompt', name: 'app_request_showcase_prompt')]
    #[HtmxOnly]
    public function requestPrompt(HtmxRequest $htmx): HtmxResponse
    {
        $promptValue = $htmx->prompt ?? '(no prompt provided)';

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('request_showcase.html.twig', 'promptResult', ['htmx' => $htmx, 'promptValue' => $promptValue])
            ->viewBlock('request_showcase.html.twig', 'requestInfoOob', ['htmx' => $htmx])
            ->build();
    }

    /**
     * Demonstrates replaceUrl - changes currentUrl for subsequent requests
     */
    #[Route('/url', name: 'app_request_showcase_url')]
    #[HtmxOnly]
    public function url(HtmxRequest $htmx): HtmxResponse
    {
        $newUrl = '/request-showcase?demo=url&t=' . time();

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('request_showcase.html.twig', 'urlResult', ['htmx' => $htmx, 'newUrl' => $newUrl])
            ->viewBlock('request_showcase.html.twig', 'requestInfoOob', ['htmx' => $htmx])
            ->replaceUrl($newUrl)
            ->build();
    }

    /**
     * Demonstrates pushUrl for history restoration test
     */
    #[Route('/history', name: 'app_request_showcase_history')]
    #[HtmxOnly]
    public function history(HtmxRequest $htmx): HtmxResponse
    {
        $newUrl = '/request-showcase?history=' . time();

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('request_showcase.html.twig', 'historyResult', ['htmx' => $htmx, 'newUrl' => $newUrl])
            ->viewBlock('request_showcase.html.twig', 'requestInfoOob', ['htmx' => $htmx])
            ->pushUrl($newUrl)
            ->build();
    }

    /**
     * Demonstrates boosted request
     */
    #[Route('/boosted', name: 'app_request_showcase_boosted', methods: ['GET', 'POST'])]
    public function boosted(HtmxRequest $htmx): HtmxResponse|RedirectResponse
    {
        if (!$htmx->isHtmx) {
            return $this->redirectToRoute('app_request_showcase');
        }

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('request_showcase.html.twig', 'boostedResult', ['htmx' => $htmx])
            ->viewBlock('request_showcase.html.twig', 'requestInfoOob', ['htmx' => $htmx])
            ->build();
    }
}
