<?php
declare(strict_types=1);

namespace App\Services;

use App\Entity\Location;
use App\Entity\Measurement;
use App\Repository\LocationRepository;
use App\Repository\MeasurementRepository;

class WeatherUtil
{
    private MeasurementRepository $measurementRepository;
    private LocationRepository $locationRepository;

    public function __construct(
        MeasurementRepository $measurementRepository,
        LocationRepository $locationRepository
    ) {
        $this->measurementRepository = $measurementRepository;
        $this->locationRepository = $locationRepository;
    }

    /**
     * @return Measurement[]
     */
    public function getWeatherForLocation(Location $location): array
    {
        return $this->measurementRepository->findBy(['location' => $location]);
    }

    /**
     * @return Measurement[]
     */
    public function getWeatherForCountryAndCity(string $countryCode, string $city): array
    {
        // Znajdź lokalizację na podstawie kodu kraju i nazwy miasta
        $location = $this->locationRepository->findOneBy([
            'country' => $countryCode,
            'city' => $city,
        ]);

        // Sprawdź, czy lokalizacja istnieje
        if ($location === null) {
            return []; // Zwróć pustą tablicę, jeśli lokalizacja nie została znaleziona
        }

        // Pobierz pomiary dla znalezionej lokalizacji
        return $this->getWeatherForLocation($location);
    }
}
