<?php

namespace App\Application\Commands;

class UpdateCoasterCommand
{
    public string $coasterId;
    public int $staffCount;
    public int $dailyClients;
    public string $operatingHoursFrom;
    public string $operatingHoursTo;

    public function __construct(
        string $coasterId,
        int $staffCount,
        int $dailyClients,
        string $operatingHoursFrom,
        string $operatingHoursTo
    ) {
        $this->coasterId = $coasterId;
        $this->staffCount = $staffCount;
        $this->dailyClients = $dailyClients;
        $this->operatingHoursFrom = $operatingHoursFrom;
        $this->operatingHoursTo = $operatingHoursTo;
    }
}
