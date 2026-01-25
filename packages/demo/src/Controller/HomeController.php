<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'app_home')]
#[Cache(maxage: 86400, public: true)]
final class HomeController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('index.html.twig');
    }
}
