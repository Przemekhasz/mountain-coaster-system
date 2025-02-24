<?php

namespace App\Application\Handlers;

use App\Application\Commands\UpdateCoasterCommand;
use App\Domain\ValueObjects\OperatingHours;
use App\Infrastructure\Persistence\CoasterRepositoryInterface;
use App\Infrastructure\Services\EventDispatcherInterface;

class UpdateCoasterHandler
{
    private CoasterRepositoryInterface $coasterRepository;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        CoasterRepositoryInterface $coasterRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->coasterRepository = $coasterRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(UpdateCoasterCommand $command): bool
    {
        $coaster = $this->coasterRepository->getById($command->coasterId);
        if (!$coaster) {
            throw new \DomainException("Coaster with ID {$command->coasterId} not found");
        }

        $operatingHours = new OperatingHours(
            $command->operatingHoursFrom,
            $command->operatingHoursTo
        );

        $coaster->updateDetails(
            $command->staffCount,
            $command->dailyClients,
            $operatingHours
        );

        $this->coasterRepository->save($coaster);

        foreach ($coaster->releaseEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        return true;
    }
}
