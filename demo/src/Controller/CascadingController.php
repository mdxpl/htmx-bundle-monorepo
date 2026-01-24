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

#[Route('/cascading')]
final class CascadingController extends AbstractController
{
    private const LOCATIONS = [
        'usa' => [
            'name' => 'United States',
            'cities' => [
                'nyc' => 'New York',
                'la' => 'Los Angeles',
                'chi' => 'Chicago',
                'hou' => 'Houston',
            ],
        ],
        'uk' => [
            'name' => 'United Kingdom',
            'cities' => [
                'lon' => 'London',
                'man' => 'Manchester',
                'bir' => 'Birmingham',
                'edi' => 'Edinburgh',
            ],
        ],
        'de' => [
            'name' => 'Germany',
            'cities' => [
                'ber' => 'Berlin',
                'mun' => 'Munich',
                'ham' => 'Hamburg',
                'fra' => 'Frankfurt',
            ],
        ],
        'pl' => [
            'name' => 'Poland',
            'cities' => [
                'war' => 'Warsaw',
                'kra' => 'Krakow',
                'wro' => 'Wroclaw',
                'gda' => 'Gdansk',
            ],
        ],
    ];

    #[Route('', name: 'app_cascading')]
    public function index(HtmxRequest $htmx): HtmxResponse
    {
        $countries = array_map(static fn($data) => $data['name'], self::LOCATIONS);

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->view('cascading.html.twig', ['countries' => $countries])
            ->build();
    }

    #[Route('/cities/{country}', name: 'app_cascading_cities')]
    #[HtmxOnly]
    public function cities(HtmxRequest $htmx, string $country): HtmxResponse
    {
        $cities = self::LOCATIONS[$country]['cities'] ?? [];

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('cascading.html.twig', 'cityOptions', ['cities' => $cities])
            ->build();
    }

    #[Route('/submit', name: 'app_cascading_submit', methods: ['POST'])]
    #[HtmxOnly]
    public function submit(HtmxRequest $htmx, Request $request): HtmxResponse
    {
        $country = $request->request->get('country');
        $city = $request->request->get('city');

        $countryName = self::LOCATIONS[$country]['name'] ?? $country;
        $cityName = self::LOCATIONS[$country]['cities'][$city] ?? $city;

        return HtmxResponseBuilder::create($htmx->isHtmx)
            ->success()
            ->viewBlock('cascading.html.twig', 'result', [
                'country' => $countryName,
                'city' => $cityName,
            ])
            ->build();
    }
}
