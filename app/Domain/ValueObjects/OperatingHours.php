<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

class OperatingHours
{
    private string $from;
    private string $to;
    private int $fromMinutes;
    private int $toMinutes;

    public function __construct(string $from, string $to)
    {
        if (!preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $from) ||
            !preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $to)) {
            throw new InvalidArgumentException('Invalid time format. Use HH:MM format.');
        }

        $this->from = $from;
        $this->to = $to;

        // Convert to minutes for easier calculations
        [$fromHours, $fromMins] = explode(':', $from);
        [$toHours, $toMins] = explode(':', $to);

        $this->fromMinutes = (int)$fromHours * 60 + (int)$fromMins;
        $this->toMinutes = (int)$toHours * 60 + (int)$toMins;

        if ($this->fromMinutes >= $this->toMinutes) {
            throw new InvalidArgumentException('Opening time must be before closing time.');
        }
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getTotalOperatingMinutes(): int
    {
        return $this->toMinutes - $this->fromMinutes;
    }

    public function canCompleteRide(int $rideTimeMinutes, int $breakTimeMinutes = 5): bool
    {
        return ($this->toMinutes - $this->fromMinutes) >= ($rideTimeMinutes + $breakTimeMinutes);
    }

    public function getMaximumRidesPerDay(int $rideTimeMinutes, int $breakTimeMinutes = 5): int
    {
        if ($rideTimeMinutes <= 0) {
            throw new InvalidArgumentException('Ride time must be positive.');
        }

        $totalTime = $this->getTotalOperatingMinutes();
        $cycleTime = $rideTimeMinutes + $breakTimeMinutes;

        return floor($totalTime / $cycleTime);
    }
}
