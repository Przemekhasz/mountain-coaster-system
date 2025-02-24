<?php

namespace App\Application\Queries;

use App\Application\DTOs\CoasterDTO;
use App\Infrastructure\Persistence\CoasterRepositoryInterface;

class GetCoasterDetailsHandler
{
    private CoasterRepositoryInterface $coasterRepository;

    public function __construct(CoasterRepositoryInterface $coasterRepository)
    {
        $this->coasterRepository = $coasterRepository;
    }

    public function handle(GetCoasterDetailsQuery $query): ?CoasterDTO
    {
        $coaster = $this->coasterRepository->getById($query->coasterId);

        if (!$coaster) {
            return null;
        }

        return CoasterDTO::fromCoaster($coaster);
    }
}
