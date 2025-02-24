<?php

namespace App\Infrastructure\Services;

use App\Domain\Events\ClientCapacityIssue;
use App\Domain\Events\StaffShortage;
use CodeIgniter\Log\Logger;

class LoggingService
{
    private Logger $logger;

    public function __construct(Logger $logger, EventDispatcherInterface $eventDispatcher)
    {
        $this->logger = $logger;

        $eventDispatcher->addListener(StaffShortage::class, [$this, 'onStaffShortage']);
        $eventDispatcher->addListener(ClientCapacityIssue::class, [$this, 'onClientCapacityIssue']);
    }

    public function onStaffShortage(StaffShortage $event): void
    {
        $coaster = $event->getCoaster();
        $shortage = $event->getShortageCount();

        $message = "Kolejka {$coaster->getId()} - Problem: Brakuje {$shortage} pracowników";
        $this->logger->warning($message);
    }

    public function onClientCapacityIssue(ClientCapacityIssue $event): void
    {
        $coaster = $event->getCoaster();
        $wagonsNeeded = $event->getWagonsNeeded();
        $staffNeeded = $event->getStaffNeeded();

        $message = "Kolejka {$coaster->getId()} - Problem: Brakuje {$staffNeeded} pracowników, brak {$wagonsNeeded} wagonów";
        $this->logger->warning($message);
    }
}

