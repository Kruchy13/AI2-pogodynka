<?php

namespace App\Command;

use App\Repository\LocationRepository;
use App\Services\WeatherUtil;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'weather:location',
    description: 'Display weather forecast for a specific location by ID',
)]
class WeatherLocationCommand extends Command
{
    private WeatherUtil $weatherUtil;
    private LocationRepository $locationRepository;

    public function __construct(WeatherUtil $weatherUtil, LocationRepository $locationRepository)
    {
        parent::__construct();
        $this->weatherUtil = $weatherUtil;
        $this->locationRepository = $locationRepository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED, 'ID of the location');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $locationId = $input->getArgument('id');

        // Znajdź lokalizację na podstawie ID
        $location = $this->locationRepository->find($locationId);

        if (!$location) {
            $io->error(sprintf('Location with ID %s not found.', $locationId));
            return Command::FAILURE;
        }

        // Pobierz prognozę pogody dla lokalizacji
        $measurements = $this->weatherUtil->getWeatherForLocation($location);

        // Wyświetl wyniki
        $io->section(sprintf('Weather forecast for %s (%s)', $location->getCity(), $location->getCountry()));
        foreach ($measurements as $measurement) {
            $io->writeln(sprintf(
                "Date: %s, Temperature: %s°C",
                $measurement->getDate()->format('Y-m-d'),
                $measurement->getCelsius()
            ));
        }

        $io->success('Weather forecast displayed successfully.');

        return Command::SUCCESS;
    }
}
