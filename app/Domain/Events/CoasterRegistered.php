<?php

namespace App\Domain\Events;

use App\Domain\Entities\Coaster;

class CoasterRegistered
{
    private Coaster $coaster;
    private \DateTimeImmutable $occurredOn;

    public function __construct(Coaster $coaster)
    {
        $this->coaster = $coaster;
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function getCoaster(): Coaster
    {
        return $this->coaster;
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
