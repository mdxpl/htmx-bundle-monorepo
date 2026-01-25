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

#[Route('/items')]
final class ItemListController extends AbstractController
{
    private const DEFAULT_ITEMS = [
        ['id' => 1, 'name' => 'Project Alpha', 'status' => 'active'],
        ['id' => 2, 'name' => 'Project Beta', 'status' => 'pending'],
        ['id' => 3, 'name' => 'Project Gamma', 'status' => 'active'],
        ['id' => 4, 'name' => 'Project Delta', 'status' => 'completed'],
        ['id' => 5, 'name' => 'Project Epsilon', 'status' => 'active'],
    ];

    private const TEMPLATE = 'item_list.html.twig';

    #[Route('', name: 'app_items')]
    public function index(HtmxRequest $htmx, Request $request): HtmxResponse
    {
        $items = $this->getItems($request);

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->view(self::TEMPLATE, ['items' => $items])
            ->build();
    }

    #[Route('/{id}/edit', name: 'app_items_edit', methods: ['GET'])]
    #[HtmxOnly]
    public function edit(HtmxRequest $htmx, Request $request, int $id): HtmxResponse
    {
        $items = $this->getItems($request);
        $item = $this->findItem($items, $id);

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock(self::TEMPLATE, 'editName', ['item' => $item])
            ->build();
    }

    #[Route('/{id}', name: 'app_items_update', methods: ['PUT'])]
    #[HtmxOnly]
    public function update(HtmxRequest $htmx, Request $request, int $id): HtmxResponse
    {
        $items = $this->getItems($request);
        $newName = trim($request->request->getString('name'));
        $item = $this->findItem($items, $id);

        if ($newName === '') {
            return HtmxResponseBuilder::create($htmx->isHtmx)
                ->failure()
                ->viewBlock(self::TEMPLATE, 'editName', ['item' => $item])
                ->viewBlock(self::TEMPLATE, 'notificationOob', ['type' => 'error', 'message' => 'Name cannot be empty'])
                ->build();
        }

        if (\strlen($newName) < 3) {
            return HtmxResponseBuilder::create($htmx->isHtmx)
                ->failure()
                ->viewBlock(self::TEMPLATE, 'editName', ['item' => $item])
                ->viewBlock(self::TEMPLATE, 'notificationOob', ['type' => 'error', 'message' => 'Name must be at least 3 characters'])
                ->build();
        }

        foreach ($items as &$itemRef) {
            if ($itemRef['id'] === $id) {
                $itemRef['name'] = $newName;
                break;
            }
        }

        $this->saveItems($request, $items);
        $item = $this->findItem($items, $id);

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock(self::TEMPLATE, 'viewName', ['item' => $item])
            ->viewBlock(self::TEMPLATE, 'notificationOob', ['type' => 'success', 'message' => "'{$newName}' name updated"])
            ->build();
    }

    #[Route('/{id}/status', name: 'app_items_status', methods: ['PUT'])]
    #[HtmxOnly]
    public function updateStatus(HtmxRequest $htmx, Request $request, int $id): HtmxResponse
    {
        $items = $this->getItems($request);
        $newStatus = $request->request->getString('status');
        $itemName = $this->findItem($items, $id)['name'] ?? 'Item';

        foreach ($items as &$item) {
            if ($item['id'] === $id) {
                $item['status'] = $newStatus;
                break;
            }
        }

        $this->saveItems($request, $items);
        $item = $this->findItem($items, $id);

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock(self::TEMPLATE, 'viewStatus', ['item' => $item])
            ->viewBlock(self::TEMPLATE, 'notificationOob', ['type' => 'success', 'message' => "'{$itemName}' status updated"])
            ->build();
    }

    #[Route('/{id}', name: 'app_items_delete', methods: ['DELETE'])]
    #[HtmxOnly]
    public function delete(HtmxRequest $htmx, Request $request, int $id): HtmxResponse
    {
        $items = $this->getItems($request);
        $deletedItem = null;

        foreach ($items as $key => $item) {
            if ($item['id'] === $id) {
                $deletedItem = $item;
                unset($items[$key]);
                break;
            }
        }

        $items = array_values($items);
        $this->saveItems($request, $items);

        $deletedName = $deletedItem['name'] ?? 'Unknown';
        $builder = HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock(self::TEMPLATE, 'empty')
            ->viewBlock(self::TEMPLATE, 'notificationOob', ['type' => 'success', 'message' => "'{$deletedName}' deleted"]);

        if ($items === []) {
            $builder->viewBlock(self::TEMPLATE, 'itemsListOob', ['items' => []]);
        }

        return $builder->build();
    }

    #[Route('/reset', name: 'app_items_reset', methods: ['POST'])]
    #[HtmxOnly]
    public function reset(HtmxRequest $htmx, Request $request): HtmxResponse
    {
        $this->saveItems($request, self::DEFAULT_ITEMS);

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock(self::TEMPLATE, 'itemsList', ['items' => self::DEFAULT_ITEMS])
            ->build();
    }

    /**
     * @return array<int, array{id: int, name: string, status: string}>
     */
    private function getItems(Request $request): array
    {
        $items = $request->getSession()->get('item_list_items');

        if ($items === null) {
            $items = self::DEFAULT_ITEMS;
            $this->saveItems($request, $items);
        }

        return $items;
    }

    /**
     * @param array<int, array{id: int, name: string, status: string}> $items
     */
    private function saveItems(Request $request, array $items): void
    {
        $request->getSession()->set('item_list_items', $items);
    }

    /**
     * @param array<int, array{id: int, name: string, status: string}> $items
     *
     * @return array{id: int, name: string, status: string}|null
     */
    private function findItem(array $items, int $id): ?array
    {
        foreach ($items as $item) {
            if ($item['id'] === $id) {
                return $item;
            }
        }

        return null;
    }
}
