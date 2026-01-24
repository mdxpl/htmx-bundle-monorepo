<?php

declare(strict_types=1);

namespace App\Controller;

use Mdxpl\HtmxBundle\Attribute\HtmxOnly;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Mdxpl\HtmxBundle\Response\Swap\Modifiers\TimingSwap;
use Mdxpl\HtmxBundle\Response\Swap\Modifiers\Transition;
use Mdxpl\HtmxBundle\Response\Swap\SwapStyle;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/builder-showcase')]
final class BuilderShowcaseController extends AbstractController
{
    #[Route('', name: 'app_builder_showcase')]
    public function index(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->view('builder_showcase.html.twig', [
                'logs' => [],
                'counter' => 0,
                'items' => [],
            ])
            ->build();
    }

    /**
     * Demonstrates: trigger() - fires JS event immediately
     */
    #[Route('/trigger', name: 'app_builder_showcase_trigger')]
    #[HtmxOnly]
    public function trigger(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('builder_showcase.html.twig', 'triggerResultOob', [
                'triggerType' => 'trigger()',
                'message' => 'Event fired IMMEDIATELY when response received',
            ])
            ->viewBlock('builder_showcase.html.twig', 'logEntryOob', [
                'log' => ['method' => 'trigger()', 'description' => 'HX-Trigger header sent, event fires immediately'],
            ])
            ->trigger('demoEvent')
            ->build();
    }

    /**
     * Demonstrates: triggerAfterSwap() - fires after DOM swap
     */
    #[Route('/trigger-after-swap', name: 'app_builder_showcase_trigger_after_swap')]
    #[HtmxOnly]
    public function triggerAfterSwap(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('builder_showcase.html.twig', 'triggerResultOob', [
                'triggerType' => 'triggerAfterSwap()',
                'message' => 'Event fired AFTER content swapped into DOM',
            ])
            ->viewBlock('builder_showcase.html.twig', 'logEntryOob', [
                'log' => ['method' => 'triggerAfterSwap()', 'description' => 'HX-Trigger-After-Swap header sent'],
            ])
            ->triggerAfterSwap('demoEvent')
            ->build();
    }

    /**
     * Demonstrates: triggerAfterSettle() - fires after CSS transitions
     */
    #[Route('/trigger-after-settle', name: 'app_builder_showcase_trigger_after_settle')]
    #[HtmxOnly]
    public function triggerAfterSettle(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('builder_showcase.html.twig', 'triggerResultOob', [
                'triggerType' => 'triggerAfterSettle()',
                'message' => 'Event fired AFTER CSS transitions completed',
            ])
            ->viewBlock('builder_showcase.html.twig', 'logEntryOob', [
                'log' => ['method' => 'triggerAfterSettle()', 'description' => 'HX-Trigger-After-Settle header sent'],
            ])
            ->triggerAfterSettle('demoEvent')
            ->build();
    }

    /**
     * Demonstrates: success() - HTTP 200 OK
     */
    #[Route('/success', name: 'app_builder_showcase_success')]
    #[HtmxOnly]
    public function success(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('builder_showcase.html.twig', 'successResult')
            ->viewBlock('builder_showcase.html.twig', 'logEntryOob', [
                'log' => ['method' => 'success()', 'description' => 'HTTP 200 OK - standard successful response'],
            ])
            ->build();
    }

    /**
     * Demonstrates: failure() - HTTP 422 Unprocessable Entity
     */
    #[Route('/failure', name: 'app_builder_showcase_failure')]
    #[HtmxOnly]
    public function failure(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->failure()
            ->viewBlock('builder_showcase.html.twig', 'failureResult')
            ->viewBlock('builder_showcase.html.twig', 'logEntryOob', [
                'log' => ['method' => 'failure()', 'description' => 'HTTP 422 - htmx swaps content thanks to beforeOnLoad handler'],
            ])
            ->build();
    }

    /**
     * Demonstrates: pushUrl() - adds URL to browser history
     */
    #[Route('/push-url', name: 'app_builder_showcase_push_url')]
    #[HtmxOnly]
    public function pushUrl(HtmxRequest $htmx): HtmxResponse
    {
        $newUrl = '/builder-showcase?demo=push-url&time=' . time();

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('builder_showcase.html.twig', 'logEntryOob', [
                'log' => ['method' => 'pushUrl()', 'description' => "URL changed to: {$newUrl} (check address bar, use back button)"],
            ])
            ->pushUrl($newUrl)
            ->build();
    }

    /**
     * Demonstrates: replaceUrl() - replaces URL without history entry
     */
    #[Route('/replace-url', name: 'app_builder_showcase_replace_url')]
    #[HtmxOnly]
    public function replaceUrl(HtmxRequest $htmx): HtmxResponse
    {
        $newUrl = '/builder-showcase?replaced=' . time();

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('builder_showcase.html.twig', 'logEntryOob', [
                'log' => ['method' => 'replaceUrl()', 'description' => "URL replaced (no history entry, back won't return here)"],
            ])
            ->replaceUrl($newUrl)
            ->build();
    }

    /**
     * Demonstrates: retarget() - server changes the swap target
     */
    #[Route('/retarget', name: 'app_builder_showcase_retarget')]
    #[HtmxOnly]
    public function retarget(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('builder_showcase.html.twig', 'retargetContent')
            ->viewBlock('builder_showcase.html.twig', 'logEntryOob', [
                'log' => ['method' => 'retarget()', 'description' => 'Button targeted #original-target, server redirected to #retarget-area'],
            ])
            ->retarget('#retarget-area')
            ->build();
    }

    /**
     * Demonstrates: withReswap() with beforeend - append to list
     */
    #[Route('/reswap-append', name: 'app_builder_showcase_reswap_append')]
    #[HtmxOnly]
    public function reswapAppend(HtmxRequest $htmx): HtmxResponse
    {
        $itemId = time();

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('builder_showcase.html.twig', 'newItem', ['itemId' => $itemId])
            ->viewBlock('builder_showcase.html.twig', 'logEntryOob', [
                'log' => ['method' => 'withReswap(BEFORE_END)', 'description' => "Item #{$itemId} appended to list (not replaced)"],
            ])
            ->withReswap(SwapStyle::BEFORE_END)
            ->build();
    }

    /**
     * Demonstrates: withReswap() with Transition modifier
     */
    #[Route('/reswap-transition', name: 'app_builder_showcase_reswap_transition')]
    #[HtmxOnly]
    public function reswapTransition(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('builder_showcase.html.twig', 'transitionContent')
            ->viewBlock('builder_showcase.html.twig', 'logEntryOob', [
                'log' => ['method' => 'withReswap() + Transition', 'description' => 'Content swapped with View Transition API animation'],
            ])
            ->retarget('#transition-area')
            ->withReswap(SwapStyle::INNER_HTML, new Transition())
            ->build();
    }

    /**
     * Demonstrates: withReswap() with TimingSwap modifier
     */
    #[Route('/reswap-timing', name: 'app_builder_showcase_reswap_timing')]
    #[HtmxOnly]
    public function reswapTiming(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('builder_showcase.html.twig', 'timingContent')
            ->viewBlock('builder_showcase.html.twig', 'logEntryOob', [
                'log' => ['method' => 'withReswap() + TimingSwap(1000)', 'description' => 'Swap delayed by 1 second'],
            ])
            ->retarget('#timing-area')
            ->withReswap(SwapStyle::INNER_HTML, new TimingSwap(1000))
            ->build();
    }

    /**
     * Demonstrates: noContent() - 204 response, only headers processed
     */
    #[Route('/no-content', name: 'app_builder_showcase_no_content')]
    #[HtmxOnly]
    public function noContent(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->noContent()
            ->trigger(['noContentDemo' => ['message' => 'HTTP 204 - no body, but trigger header works!']])
            ->build();
    }

    /**
     * Demonstrates: reselect() - server selects part of response
     */
    #[Route('/reselect', name: 'app_builder_showcase_reselect')]
    #[HtmxOnly]
    public function reselect(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('builder_showcase.html.twig', 'reselectFullResponse')
            ->viewBlock('builder_showcase.html.twig', 'logEntryOob', [
                'log' => ['method' => 'reselect()', 'description' => 'Full response sent, but only #selected-part was swapped'],
            ])
            ->retarget('#reselect-area')
            ->reselect('#selected-part')
            ->build();
    }

    /**
     * Demonstrates: multiple OOB updates in single response
     */
    #[Route('/multiple-oob', name: 'app_builder_showcase_multiple_oob')]
    #[HtmxOnly]
    public function multipleOob(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('builder_showcase.html.twig', 'oobArea1')
            ->viewBlock('builder_showcase.html.twig', 'oobArea2')
            ->viewBlock('builder_showcase.html.twig', 'oobArea3')
            ->viewBlock('builder_showcase.html.twig', 'logEntryOob', [
                'log' => ['method' => 'Multiple viewBlock()', 'description' => '3 different areas updated with single request'],
            ])
            ->build();
    }

    /**
     * Demonstrates: location() - client-side navigation without full reload
     */
    #[Route('/location', name: 'app_builder_showcase_location')]
    #[HtmxOnly]
    public function location(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->location($this->generateUrl('app_builder_showcase') . '?from=location')
            ->build();
    }

    /**
     * Clears the log
     */
    #[Route('/clear-log', name: 'app_builder_showcase_clear_log')]
    #[HtmxOnly]
    public function clearLog(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('builder_showcase.html.twig', 'logListOob', ['logs' => []])
            ->build();
    }
}
