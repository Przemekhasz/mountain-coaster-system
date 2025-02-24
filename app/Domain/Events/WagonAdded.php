<?php

namespace App\Domain\Events;

use App\Domain\Entities\Coaster;
use App\Domain\Entities\Wagon;

class WagonAdded
{
    private Coaster $coaster;
    private Wagon $wagon;
    private \DateTimeImmutable $occurredOn;

    public function __construct(Coaster $coaster, Wagon $wagon)
    {
        $this->coaster = $coaster;
        $this->wagon = $wagon;
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function getCoaster(): Coaster
    {
        return $this->coaster;
    }

    public function getWagon(): Wagon
    {
        return $this->wagon;
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
