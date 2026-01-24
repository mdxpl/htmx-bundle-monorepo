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

#[Route('/click-to-edit')]
final class ClickToEditController extends AbstractController
{
    private const PROFILE = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'bio' => 'Software developer passionate about clean code.',
    ];

    #[Route('', name: 'app_click_to_edit')]
    public function index(HtmxRequest $htmx): HtmxResponse
    {
        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->view('click_to_edit.html.twig', ['profile' => self::PROFILE])
            ->build();
    }

    #[Route('/field/{field}', name: 'app_click_to_edit_field', methods: ['GET'])]
    #[HtmxOnly]
    public function editField(HtmxRequest $htmx, string $field): HtmxResponse
    {
        $value = self::PROFILE[$field] ?? '';

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('click_to_edit.html.twig', 'editField', ['field' => $field, 'value' => $value])
            ->build();
    }

    #[Route('/field/{field}', name: 'app_click_to_edit_save', methods: ['POST'])]
    #[HtmxOnly]
    public function saveField(HtmxRequest $htmx, Request $request, string $field): HtmxResponse
    {
        $value = $request->request->get('value', '');
        usleep(300000);

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('click_to_edit.html.twig', 'viewField', ['field' => $field, 'value' => $value])
            ->build();
    }
}
