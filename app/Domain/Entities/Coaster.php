<?php

namespace App\Domain\Entities;

use App\Domain\Events\ClientCapacityIssue;
use App\Domain\Events\CoasterRegistered;
use App\Domain\Events\StaffShortage;
use App\Domain\Events\WagonAdded;
use App\Domain\ValueObjects\OperatingHours;

class Coaster
{
    private string $id;
    private int $staffCount;
    private int $dailyClients;
    private float $trackLength;
    private OperatingHours $operatingHours;
    private array $wagons = [];
    private array $events = [];

    public function __construct(
        string $id,
        int $staffCount,
        int $dailyClients,
        float $trackLength,
        OperatingHours $operatingHours
    ) {
        $this->id = $id;
        $this->staffCount = $staffCount;
        $this->dailyClients = $dailyClients;
        $this->trackLength = $trackLength;
        $this->operatingHours = $operatingHours;

        $this->raiseEvent(new CoasterRegistered($this));
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStaffCount(): int
    {
        return $this->staffCount;
    }

    public function getDailyClients(): int
    {
        return $this->dailyClients;
    }

    public function getTrackLength(): float
    {
        return $this->trackLength;
    }

    public function getOperatingHours(): OperatingHours
    {
        return $this->operatingHours;
    }

    public function getWagons(): array
    {
        return $this->wagons;
    }

    public function updateDetails(int $staffCount, int $dailyClients, OperatingHours $operatingHours): void
    {
        $this->staffCount = $staffCount;
        $this->dailyClients = $dailyClients;
        $this->operatingHours = $operatingHours;

        // Revalidate capacity and staff requirements
        $this->validateStaffingLevels();
        $this->validateClientCapacity();
    }

    public function addWagon(Wagon $wagon): void
    {
        $this->wagons[$wagon->getId()] = $wagon;
        $this->raiseEvent(new WagonAdded($this, $wagon));

        // Revalidate capacity and staff requirements
        $this->validateStaffingLevels();
        $this->validateClientCapacity();
    }

    public function removeWagon(string $wagonId): bool
    {
        if (!isset($this->wagons[$wagonId])) {
            return false;
        }

        unset($this->wagons[$wagonId]);

        // Revalidate capacity and staff requirements
        $this->validateStaffingLevels();
        $this->validateClientCapacity();

        return true;
    }

    public function validateStaffingLevels(): void
    {
        $requiredStaff = $this->calculateRequiredStaff();

        if ($this->staffCount < $requiredStaff) {
            $shortage = $requiredStaff - $this->staffCount;
            $this->raiseEvent(new StaffShortage($this, $shortage));
        } elseif ($this->staffCount > $requiredStaff) {
            $surplus = $this->staffCount - $requiredStaff;
        }
    }

    public function validateClientCapacity(): void
    {
        $capacity = $this->calculateDailyClientCapacity();

        if ($capacity < $this->dailyClients) {
            $deficit = $this->dailyClients - $capacity;
            $wagonsNeeded = $this->calculateAdditionalWagonsNeeded($deficit);
            $staffNeeded = $wagonsNeeded * 2;

            $this->raiseEvent(new ClientCapacityIssue($this, $deficit, $wagonsNeeded, $staffNeeded));
        } elseif ($capacity > $this->dailyClients * 2) {
            $surplus = $capacity - $this->dailyClients;
            // TODO could raise an event for excess capacity if needed
        }
    }

    public function calculateRequiredStaff(): int
    {
        // 1 staff for the coaster + 2 staff per wagon
        return 1 + (count($this->wagons) * 2);
    }

    public function calculateDailyClientCapacity(): int
    {
        if (empty($this->wagons)) {
            return 0;
        }

        $totalSeats = 0;
        $averageRideTimeMinutes = 0;

        foreach ($this->wagons as $wagon) {
            $totalSeats += $wagon->getSeatCount();
            $averageRideTimeMinutes += $wagon->calculateRideTimeInMinutes($this->trackLength);
        }

        $averageRideTimeMinutes = $averageRideTimeMinutes / count($this->wagons);

        $maxRidesPerDay = $this->operatingHours->getMaximumRidesPerDay($averageRideTimeMinutes);

        return $totalSeats * count($this->wagons) * $maxRidesPerDay;
    }

    public function calculateAdditionalWagonsNeeded(int $clientDeficit): int
    {
        if (empty($this->wagons)) {
            return ceil($this->dailyClients / 1000);
        }

        // Use existing wagons as a benchmark
        $currentCapacity = $this->calculateDailyClientCapacity();
        $existingWagons = count($this->wagons);

        if ($currentCapacity <= 0) {
            return 1;
        }

        $capacityPerWagon = $currentCapacity / $existingWagons;
        return ceil($clientDeficit / $capacityPerWagon);
    }

    private function raiseEvent(object $event): void
    {
        $this->events[] = $event;
        // TODO would be dispatched to event handlers
    }

    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }
}
