<?php

namespace App\Application\Commands;

class RegisterCoasterCommand
{
    public string $coasterId;
    public int $staffCount;
    public int $dailyClients;
    public float $trackLength;
    public string $operatingHoursFrom;
    public string $operatingHoursTo;

    public function __construct(
        string $coasterId,
        int $staffCount,
        int $dailyClients,
        float $trackLength,
        string $operatingHoursFrom,
        string $operatingHoursTo
    ) {
        $this->coasterId = $coasterId;
        $this->staffCount = $staffCount;
        $this->dailyClients = $dailyClients;
        $this->trackLength = $trackLength;
        $this->operatingHoursFrom = $operatingHoursFrom;
        $this->operatingHoursTo = $operatingHoursTo;
    }
}
