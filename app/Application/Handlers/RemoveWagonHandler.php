<?php

namespace App\Application\Handlers;

use App\Application\Commands\RemoveWagonCommand;
use App\Infrastructure\Persistence\CoasterRepositoryInterface;
use App\Infrastructure\Persistence\WagonRepositoryInterface;
use App\Infrastructure\Services\EventDispatcherInterface;

class RemoveWagonHandler
{
    private CoasterRepositoryInterface $coasterRepository;
    private WagonRepositoryInterface $wagonRepository;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        CoasterRepositoryInterface $coasterRepository,
        WagonRepositoryInterface $wagonRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->coasterRepository = $coasterRepository;
        $this->wagonRepository = $wagonRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(RemoveWagonCommand $command): bool
    {
        $coaster = $this->coasterRepository->getById($command->coasterId);
        if (!$coaster) {
            throw new \DomainException("Coaster with ID {$command->coasterId} not found");
        }

        $success = $coaster->removeWagon($command->wagonId);
        if (!$success) {
            return false;
        }

        $this->wagonRepository->delete($command->wagonId);

        $this->coasterRepository->save($coaster);

        foreach ($coaster->releaseEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        return true;
    }
}
