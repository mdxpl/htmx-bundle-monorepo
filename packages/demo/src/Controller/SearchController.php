<?php

declare(strict_types=1);

namespace App\Controller;

use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/search', name: 'app_search')]
final class SearchController extends AbstractController
{
    private const USERS = [
        ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
        ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
        ['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com'],
        ['id' => 4, 'name' => 'Alice Brown', 'email' => 'alice@example.com'],
        ['id' => 5, 'name' => 'Charlie Wilson', 'email' => 'charlie@example.com'],
        ['id' => 6, 'name' => 'Diana Miller', 'email' => 'diana@example.com'],
        ['id' => 7, 'name' => 'Edward Davis', 'email' => 'edward@example.com'],
        ['id' => 8, 'name' => 'Fiona Garcia', 'email' => 'fiona@example.com'],
    ];

    public function __invoke(HtmxRequest $htmx, #[MapQueryParameter] string $q = ''): HtmxResponse
    {
        $results = [];
        if (strlen($q) >= 2) {
            $results = array_filter(self::USERS, static fn($user) =>
                stripos($user['name'], $q) !== false || stripos($user['email'], $q) !== false
            );
            usleep(200000);
        }

        $viewData = ['query' => $q, 'results' => array_values($results)];
        $builder = HtmxResponseBuilder::create($htmx->isHtmx);

        if ($htmx->isHtmx) {
            return $builder
                ->success()
                ->viewBlock('search.html.twig', 'results', $viewData)
                ->build();
        }

        return $builder->success()->view('search.html.twig', $viewData)->build();
    }
}
