<?php

namespace App\Application\DTOs;


use App\Domain\Entities\Wagon;

class WagonDTO
{
    public string $id;
    public string $coasterId;
    public int $seatCount;
    public float $speed;
    public int $rideTimeMinutes;

    public static function fromWagon(Wagon $wagon): self
    {
        $dto = new self();
        $dto->id = $wagon->getId();
        $dto->coasterId = $wagon->getCoasterId();
        $dto->seatCount = $wagon->getSeatCount();
        $dto->speed = $wagon->getSpeed()->getValue();

        return $dto;
    }
}
