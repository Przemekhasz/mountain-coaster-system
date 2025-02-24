<?php

namespace App\Application\Queries;

class GetCoasterDetailsQuery
{
    public string $coasterId;

    public function __construct(string $coasterId)
    {
        $this->coasterId = $coasterId;
    }
}
