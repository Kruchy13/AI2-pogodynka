<?php
namespace App\Tests\Entity;

use App\Entity\Measurement;
use PHPUnit\Framework\TestCase;

class MeasurementTest extends TestCase
{
    /**
     * @dataProvider dataGetFahrenheit
     */
    public function testGetFahrenheit($celsius, $expectedFahrenheit): void
    {
        $measurement = new Measurement();
        $measurement->setCelsius($celsius);
        $this->assertEquals($expectedFahrenheit, $measurement->getFahrenheit(), "{$celsius}°C should be {$expectedFahrenheit}°F");
    }

    public function dataGetFahrenheit(): array
    {
        return [
            ['0', 32],
            ['-100', -148],
            ['100', 212],
            ['0.5', 32.9],
            ['-17.78', 0],
            ['37', 98.6],
            ['-40', -40],
            ['20', 68],
            ['50', 122],
            ['-273.15', -459.67], // Zero absolutne
        ];
    }
}

