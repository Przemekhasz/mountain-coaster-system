<?php

namespace App\Application\Handlers;

use App\Application\Commands\RegisterCoasterCommand;
use App\Domain\Entities\Coaster;
use App\Domain\ValueObjects\OperatingHours;
use App\Infrastructure\Persistence\CoasterRepositoryInterface;
use App\Infrastructure\Services\EventDispatcherInterface;
use App\Infrastructure\Services\IdGeneratorInterface;

class RegisterCoasterHandler
{
    private CoasterRepositoryInterface $coasterRepository;
    private EventDispatcherInterface $eventDispatcher;
    private IdGeneratorInterface $idGenerator;

    public function __construct(
        CoasterRepositoryInterface $coasterRepository,
        EventDispatcherInterface $eventDispatcher,
        IdGeneratorInterface $idGenerator
    ) {
        $this->coasterRepository = $coasterRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->idGenerator = $idGenerator;
    }

    public function handle(RegisterCoasterCommand $command): string
    {
        $operatingHours = new OperatingHours(
            $command->operatingHoursFrom,
            $command->operatingHoursTo
        );

        $coasterId = $command->coasterId ?: $this->idGenerator->generate();

        $coaster = new Coaster(
            $coasterId,
            $command->staffCount,
            $command->dailyClients,
            $command->trackLength,
            $operatingHours
        );

        $this->coasterRepository->save($coaster);

        foreach ($coaster->releaseEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        return $coasterId;
    }
}

