<?php

namespace App\Application\Queries;

use App\Application\DTOs\CoasterDTO;
use App\Infrastructure\Persistence\CoasterRepositoryInterface;

class GetSystemStatisticsHandler
{
    private CoasterRepositoryInterface $coasterRepository;

    public function __construct(CoasterRepositoryInterface $coasterRepository)
    {
        $this->coasterRepository = $coasterRepository;
    }

    public function handle(GetSystemStatisticsQuery $query): array
    {
        $coasters = $this->coasterRepository->getAll();

        $statistics = [
            'totalCoasters' => count($coasters),
            'totalWagons' => 0,
            'totalStaff' => 0,
            'requiredStaff' => 0,
            'totalDailyClients' => 0,
            'totalClientCapacity' => 0,
            'coasters' => []
        ];

        foreach ($coasters as $coaster) {
            $coasterDTO = CoasterDTO::fromCoaster($coaster);
            $statistics['coasters'][] = $coasterDTO;

            $statistics['totalWagons'] += count($coaster->getWagons());
            $statistics['totalStaff'] += $coaster->getStaffCount();
            $statistics['requiredStaff'] += $coaster->calculateRequiredStaff();
            $statistics['totalDailyClients'] += $coaster->getDailyClients();
            $statistics['totalClientCapacity'] += $coaster->calculateDailyClientCapacity();
        }

        $statistics['staffBalance'] = $statistics['totalStaff'] - $statistics['requiredStaff'];
        $statistics['capacityBalance'] = $statistics['totalClientCapacity'] - $statistics['totalDailyClients'];

        return $statistics;
    }
}
