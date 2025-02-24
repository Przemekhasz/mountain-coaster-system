<?php

namespace App\Infrastructure\Services;

interface EventDispatcherInterface
{
    public function dispatch(object $event): void;
}
