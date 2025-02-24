<?php

namespace App\Application\DTOs;

use App\Domain\Entities\Coaster;

class CoasterDTO
{
    public string $id;
    public int $staffCount;
    public int $dailyClients;
    public float $trackLength;
    public string $operatingHoursFrom;
    public string $operatingHoursTo;
    public int $requiredStaff;
    public int $currentWagonCount;
    public int $clientCapacity;
    public array $wagons = [];
    public string $status;
    public array $issues = [];

    public static function fromCoaster(Coaster $coaster): self
    {
        $dto = new self();
        $dto->id = $coaster->getId();
        $dto->staffCount = $coaster->getStaffCount();
        $dto->dailyClients = $coaster->getDailyClients();
        $dto->trackLength = $coaster->getTrackLength();
        $dto->operatingHoursFrom = $coaster->getOperatingHours()->getFrom();
        $dto->operatingHoursTo = $coaster->getOperatingHours()->getTo();
        $dto->requiredStaff = $coaster->calculateRequiredStaff();
        $dto->currentWagonCount = count($coaster->getWagons());
        $dto->clientCapacity = $coaster->calculateDailyClientCapacity();

        foreach ($coaster->getWagons() as $wagon) {
            $dto->wagons[] = WagonDTO::fromWagon($wagon);
        }

        $dto->status = 'OK';
        $dto->issues = [];

        if ($dto->staffCount < $dto->requiredStaff) {
            $dto->status = 'Problem';
            $dto->issues[] = 'Brakuje ' . ($dto->requiredStaff - $dto->staffCount) . ' pracowników';
        }

        if ($dto->clientCapacity < $dto->dailyClients) {
            $dto->status = 'Problem';
            $wagonsNeeded = $coaster->calculateAdditionalWagonsNeeded($dto->dailyClients - $dto->clientCapacity);
            $dto->issues[] = 'Brak ' . $wagonsNeeded . ' wagonów do obsługi wszystkich klientów';
        } elseif ($dto->clientCapacity > $dto->dailyClients * 2) {
            $dto->issues[] = 'Nadmiarowa liczba wagonów';
        }

        return $dto;
    }
}
