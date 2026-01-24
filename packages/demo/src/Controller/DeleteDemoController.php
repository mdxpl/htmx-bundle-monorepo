<?php

declare(strict_types=1);

namespace App\Controller;

use Mdxpl\HtmxBundle\Attribute\HtmxOnly;
use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/delete-demo')]
final class DeleteDemoController extends AbstractController
{
    private const DEFAULT_ITEMS = [
        ['id' => 1, 'name' => 'Project Alpha', 'status' => 'active'],
        ['id' => 2, 'name' => 'Project Beta', 'status' => 'pending'],
        ['id' => 3, 'name' => 'Project Gamma', 'status' => 'active'],
        ['id' => 4, 'name' => 'Project Delta', 'status' => 'completed'],
        ['id' => 5, 'name' => 'Project Epsilon', 'status' => 'active'],
    ];

    #[Route('', name: 'app_delete_demo')]
    public function index(HtmxRequest $htmx, Request $request): HtmxResponse
    {
        $items = $request->getSession()->get('delete_demo_items');
        if ($items === null) {
            $items = self::DEFAULT_ITEMS;
            $request->getSession()->set('delete_demo_items', $items);
        }

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->view('delete_demo.html.twig', ['items' => $items])
            ->build();
    }

    #[Route('/{id}', name: 'app_delete_demo_item', methods: ['DELETE'])]
    #[HtmxOnly]
    public function delete(HtmxRequest $htmx, Request $request, int $id): HtmxResponse
    {
        $items = $request->getSession()->get('delete_demo_items', []);
        $deletedItem = null;

        foreach ($items as $key => $item) {
            if ($item['id'] === $id) {
                $deletedItem = $item;
                unset($items[$key]);
                break;
            }
        }

        $request->getSession()->set('delete_demo_items', array_values($items));
        usleep(300000);

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->trigger(['showToast' => ['message' => "'{$deletedItem['name']}' has been deleted", 'type' => 'success']])
            ->viewBlock('delete_demo.html.twig', 'empty')
            ->build();
    }

    #[Route('/reset', name: 'app_delete_demo_reset', methods: ['POST'])]
    #[HtmxOnly]
    public function reset(HtmxRequest $htmx, Request $request): HtmxResponse
    {
        $request->getSession()->remove('delete_demo_items');

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->redirect($this->generateUrl('app_delete_demo'))
            ->viewBlock('delete_demo.html.twig', 'empty')
            ->build();
    }
}
