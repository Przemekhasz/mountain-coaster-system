<?php

namespace App\Infrastructure\Services;

interface IdGeneratorInterface
{
    public function generate(): string;
}
