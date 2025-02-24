<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

class Speed
{
    private float $value;
    private string $unit = 'm/s';

    public function __construct(float $value)
    {
        if ($value <= 0) {
            throw new InvalidArgumentException('Speed must be positive.');
        }
        $this->value = $value;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function calculateRideTime(float $distanceInMeters): int
    {
        return (int)ceil($distanceInMeters / $this->value);
    }

    public function calculateRideTimeInMinutes(float $distanceInMeters): int
    {
        $seconds = $this->calculateRideTime($distanceInMeters);
        return (int)ceil($seconds / 60);
    }
}

