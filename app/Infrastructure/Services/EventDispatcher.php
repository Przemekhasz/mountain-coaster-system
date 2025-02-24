<?php

namespace App\Infrastructure\Services;

use CodeIgniter\Log\Logger;

class EventDispatcher implements EventDispatcherInterface
{
    private Logger $logger;
    private array $listeners = [];

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function addListener(string $eventClass, callable $listener): void
    {
        if (!isset($this->listeners[$eventClass])) {
            $this->listeners[$eventClass] = [];
        }
        $this->listeners[$eventClass][] = $listener;
    }

    public function dispatch(object $event): void
    {
        $eventClass = get_class($event);

        $this->logger->info("Event dispatched: " . $eventClass);

        if (isset($this->listeners[$eventClass])) {
            foreach ($this->listeners[$eventClass] as $listener) {
                $listener($event);
            }
        }
    }
}
