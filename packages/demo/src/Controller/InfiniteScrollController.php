<?php

declare(strict_types=1);

namespace App\Controller;

use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/infinite-scroll', name: 'app_infinite_scroll')]
final class InfiniteScrollController extends AbstractController
{
    public function __invoke(HtmxRequest $htmx, #[MapQueryParameter] int $page = 1): HtmxResponse
    {
        $itemsPerPage = 10;
        $totalItems = 100;
        $hasMore = ($page * $itemsPerPage) < $totalItems;

        $items = [];
        $start = ($page - 1) * $itemsPerPage + 1;
        $end = min($page * $itemsPerPage, $totalItems);

        for ($i = $start; $i <= $end; $i++) {
            $items[] = [
                'id' => $i,
                'title' => "Item #{$i}",
                'description' => "This is the description for item number {$i}.",
            ];
        }

        $viewData = [
            'items' => $items,
            'currentPage' => $page,
            'nextPage' => $page + 1,
            'hasMore' => $hasMore,
            'totalPages' => (int) ceil($totalItems / $itemsPerPage),
        ];

        $builder = HtmxResponseBuilder::create($htmx->isHtmx);

        if ($htmx->isHtmx) {
            usleep(300000);

            return $builder
                ->success()
                ->viewBlock('infinite_scroll.html.twig', 'items', $viewData)
                ->build();
        }

        return $builder
            ->success()
            ->view('infinite_scroll.html.twig', $viewData)
            ->build();
    }
}
