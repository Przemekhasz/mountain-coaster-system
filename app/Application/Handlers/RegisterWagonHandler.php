<?php

namespace App\Application\Handlers;

use App\Application\Commands\RegisterWagonCommand;
use App\Domain\Entities\Wagon;
use App\Domain\ValueObjects\Speed;
use App\Infrastructure\Persistence\CoasterRepositoryInterface;
use App\Infrastructure\Persistence\WagonRepositoryInterface;
use App\Infrastructure\Services\EventDispatcherInterface;
use App\Infrastructure\Services\IdGeneratorInterface;

class RegisterWagonHandler
{
    private CoasterRepositoryInterface $coasterRepository;
    private WagonRepositoryInterface $wagonRepository;
    private EventDispatcherInterface $eventDispatcher;
    private IdGeneratorInterface $idGenerator;

    public function __construct(
        CoasterRepositoryInterface $coasterRepository,
        WagonRepositoryInterface $wagonRepository,
        EventDispatcherInterface $eventDispatcher,
        IdGeneratorInterface $idGenerator
    ) {
        $this->coasterRepository = $coasterRepository;
        $this->wagonRepository = $wagonRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->idGenerator = $idGenerator;
    }

    public function handle(RegisterWagonCommand $command): string
    {
        $coaster = $this->coasterRepository->getById($command->coasterId);
        if (!$coaster) {
            throw new \DomainException("Coaster with ID {$command->coasterId} not found");
        }

        $speed = new Speed($command->speed);

        $wagonId = $command->wagonId ?: $this->idGenerator->generate();

        $wagon = new Wagon(
            $wagonId,
            $command->coasterId,
            $command->seatCount,
            $speed
        );

        $this->wagonRepository->save($wagon);

        $coaster->addWagon($wagon);
        $this->coasterRepository->save($coaster);

        foreach ($coaster->releaseEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        return $wagonId;
    }
}
