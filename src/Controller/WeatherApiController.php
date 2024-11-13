<?php
namespace App\Controller;

use App\Entity\Measurement;
use App\Services\WeatherUtil;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

class WeatherApiController extends AbstractController
{
    private WeatherUtil $weatherUtil;

    public function __construct(WeatherUtil $weatherUtil)
    {
        $this->weatherUtil = $weatherUtil;
    }

    #[Route('/api/v1/weather', name: 'app_weather_api')]
    public function index(
        #[MapQueryParameter('country')] string $country,
        #[MapQueryParameter('city')] string $city,
        #[MapQueryParameter('format')] string $format = 'json',
        #[MapQueryParameter('twig')] bool $twig = false
    ): Response {
        $measurements = $this->weatherUtil->getWeatherForCountryAndCity($country, $city);

        // Renderowanie za pomocą TWIG, jeśli parametr 'twig' jest ustawiony na true
        if ($twig) {
            if ($format === 'json') {
                return $this->render('weather_api/index.json.twig', [
                    'country' => $country,
                    'city' => $city,
                    'measurements' => $measurements,
                ], new Response('', 200, ['Content-Type' => 'application/json']));
            } elseif ($format === 'csv') {
                return $this->render('weather_api/index.csv.twig', [
                    'country' => $country,
                    'city' => $city,
                    'measurements' => $measurements,
                ], new Response('', 200, [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="weather.csv"'
                ]));
            }
        }

        // Ręczna obsługa formatu JSON
        if ($format === 'json') {
            $measurementsJsonArray = array_map(function (Measurement $m) {
                return sprintf(
                    '{"date":"%s","celsius":%s,"fahrenheit":%s}',
                    $m->getDate()->format('Y-m-d'),
                    $m->getCelsius(),
                    $m->getFahrenheit()
                );
            }, $measurements);

            $measurementsJson = implode(",", $measurementsJsonArray);

            $jsonResponse = sprintf(
                '{"country":"%s","city":"%s","measurements":[%s]}',
                $country,
                $city,
                $measurementsJson
            );

            return new Response($jsonResponse, 200, ['Content-Type' => 'application/json']);
        }

        // Ręczna obsługa formatu CSV
        $csvData = array_map(fn(Measurement $m) => sprintf(
            '%s,%s,%s,%s,%s',
            $city,
            $country,
            $m->getDate()->format('Y-m-d'),
            $m->getCelsius(),
            $m->getFahrenheit()
        ), $measurements);

        array_unshift($csvData, 'city,country,date,celsius,fahrenheit');
        $response = new Response(implode("\n", $csvData));
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="weather.csv"');

        return $response;
    }
}
