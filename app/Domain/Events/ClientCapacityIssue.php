<?php

namespace App\Domain\Events;

use App\Domain\Entities\Coaster;

class ClientCapacityIssue
{
    private Coaster $coaster;
    private int $clientDeficit;
    private int $wagonsNeeded;
    private int $staffNeeded;
    private \DateTimeImmutable $occurredOn;

    public function __construct(Coaster $coaster, int $clientDeficit, int $wagonsNeeded, int $staffNeeded)
    {
        $this->coaster = $coaster;
        $this->clientDeficit = $clientDeficit;
        $this->wagonsNeeded = $wagonsNeeded;
        $this->staffNeeded = $staffNeeded;
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function getCoaster(): Coaster
    {
        return $this->coaster;
    }

    public function getClientDeficit(): int
    {
        return $this->clientDeficit;
    }

    public function getWagonsNeeded(): int
    {
        return $this->wagonsNeeded;
    }

    public function getStaffNeeded(): int
    {
        return $this->staffNeeded;
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
