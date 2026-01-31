<?php

declare(strict_types=1);

namespace App\Controller;

use Mdxpl\HtmxBundle\Request\HtmxRequest;
use Mdxpl\HtmxBundle\Response\HtmxResponse;
use Mdxpl\HtmxBundle\Response\HtmxResponseBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Cache(maxage: 86400, public: true, vary: ['HX-Request'])]
#[Route('/simple-page/{slug}', name: 'app_simple_page')]
final class SimplePageController extends AbstractController
{
    private const PAGES = [
        'home' => ['name' => 'Home', 'description' => 'Welcome to the htmx-bundle demo! Navigate using the menu above.'],
        'about' => ['name' => 'About', 'description' => 'This demo showcases the htmx-bundle for Symfony.'],
        'contact' => ['name' => 'Contact', 'description' => 'Get in touch with us for more information.'],
    ];

    public function __invoke(HtmxRequest $htmx, string $slug = 'home'): HtmxResponse
    {
        $page = self::PAGES[$slug] ?? throw new NotFoundHttpException('Page not found');

        $viewData = [
            'menu' => self::PAGES,
            'page' => $page,
            'currentSlug' => $slug,
        ];

        $builder = HtmxResponseBuilder::create($htmx->isHtmx);

        if ($htmx->isHtmx) {
            return $builder
                ->success()
                ->viewBlock('simple_page.html.twig', 'pageContent', $viewData)
                ->viewBlock('simple_page.html.twig', 'pageNav', $viewData)
                ->build();
        }

        return $builder
            ->success()
            ->view('simple_page.html.twig', $viewData)
            ->build();
    }
}
