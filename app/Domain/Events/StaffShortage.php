<?php

namespace App\Domain\Events;

use App\Domain\Entities\Coaster;

class StaffShortage
{
    private Coaster $coaster;
    private int $shortageCount;
    private \DateTimeImmutable $occurredOn;

    public function __construct(Coaster $coaster, int $shortageCount)
    {
        $this->coaster = $coaster;
        $this->shortageCount = $shortageCount;
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function getCoaster(): Coaster
    {
        return $this->coaster;
    }

    public function getShortageCount(): int
    {
        return $this->shortageCount;
    }

    public function getOccurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }
}
