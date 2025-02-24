<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Speed;
use InvalidArgumentException;

class Wagon
{
    private string $id;
    private string $coasterId;
    private int $seatCount;
    private Speed $speed;

    public function __construct(
        string $id,
        string $coasterId,
        int $seatCount,
        Speed $speed
    ) {
        if ($seatCount <= 0) {
            throw new InvalidArgumentException('Seat count must be positive.');
        }

        $this->id = $id;
        $this->coasterId = $coasterId;
        $this->seatCount = $seatCount;
        $this->speed = $speed;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCoasterId(): string
    {
        return $this->coasterId;
    }

    public function getSeatCount(): int
    {
        return $this->seatCount;
    }

    public function getSpeed(): Speed
    {
        return $this->speed;
    }

    public function calculateRideTime(float $trackLength): int
    {
        return $this->speed->calculateRideTime($trackLength);
    }

    public function calculateRideTimeInMinutes(float $trackLength): int
    {
        return $this->speed->calculateRideTimeInMinutes($trackLength);
    }
}
