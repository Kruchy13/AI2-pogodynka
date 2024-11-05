<?php

namespace App\Controller;

use App\Entity\Location;
use App\Repository\LocationRepository;
use App\Repository\MeasurementRepository;
use App\Services\WeatherUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeatherController extends AbstractController
{
    #[Route('/weather/{city}/{country?}', name: 'app_weather', requirements: ['country' => '[A-Z]{2}'])]
    public function city(
        string $city,
        ?string $country = null,
        LocationRepository $locationRepository,
        //MeasurementRepository $measurementRepository
        WeatherUtil $weatherUtil,
    ): Response {
        if (!$country) {
            $country = 'PL';
        }

        $location = $locationRepository->findOneBy([
            'city' => $city,
            'country' => $country,
        ]);

        if (!$location) {
            throw $this->createNotFoundException('Location not found.');
        }

        //$measurements = $measurementRepository->findByLocation($location);
        $measurements = $weatherUtil->getWeatherForLocation($location);

        return $this->render('weather/city.html.twig', [
            'location' => $location,
            'measurements' => $measurements,
        ]);
    }
}
