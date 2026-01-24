<?php

declare(strict_types=1);

namespace App\Controller;

use DateTime;
use Mdxpl\HtmxBundle\Attribute\HtmxOnly;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/polling')]
final class PollingController extends AbstractController
{
    #[Route('', name: 'app_polling')]
    public function index(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->view('polling.html.twig', ['stats' => $this->generateStats()])
            ->build();
    }

    #[Route('/stats', name: 'app_polling_stats')]
    #[HtmxOnly]
    public function stats(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('polling.html.twig', 'statsContent', ['stats' => $this->generateStats()])
            ->build();
    }

    /**
     * @return array{visitors: int, orders: int, revenue: int, cpu: int, memory: int, updated_at: string}
     */
    private function generateStats(): array
    {
        return [
            'visitors' => rand(100, 999),
            'orders' => rand(10, 99),
            'revenue' => rand(1000, 9999),
            'cpu' => rand(20, 95),
            'memory' => rand(40, 85),
            'updated_at' => new DateTime()->format('H:i:s'),
        ];
    }
}
