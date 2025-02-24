<?php

namespace App\Application\Commands;

class RemoveWagonCommand
{
    public string $coasterId;
    public string $wagonId;

    public function __construct(string $coasterId, string $wagonId)
    {
        $this->coasterId = $coasterId;
        $this->wagonId = $wagonId;
    }
}
