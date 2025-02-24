<?php

namespace App\Application\Commands;

class RegisterWagonCommand
{
    public string $wagonId;
    public string $coasterId;
    public int $seatCount;
    public float $speed;

    public function __construct(
        string $wagonId,
        string $coasterId,
        int $seatCount,
        float $speed
    ) {
        $this->wagonId = $wagonId;
        $this->coasterId = $coasterId;
        $this->seatCount = $seatCount;
        $this->speed = $speed;
    }
}
