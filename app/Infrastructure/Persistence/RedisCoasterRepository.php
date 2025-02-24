<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\Coaster;
use App\Domain\ValueObjects\OperatingHours;
use CodeIgniter\Cache\CacheInterface;

class RedisCoasterRepository implements CoasterRepositoryInterface
{
    private CacheInterface $cache;
    private string $prefix;
    private WagonRepositoryInterface $wagonRepository;

    public function __construct(
        CacheInterface $cache,
        WagonRepositoryInterface $wagonRepository,
        string $environmentPrefix = 'prod'
    ) {
        $this->cache = $cache;
        $this->wagonRepository = $wagonRepository;
        $this->prefix = $environmentPrefix . '_coasters_';
    }

    public function getById(string $id): ?Coaster
    {
        $data = $this->cache->get($this->prefix . $id);

        if (!$data) {
            return null;
        }

        return $this->hydrateCoaster($data);
    }

    public function getAll(): array
    {
        $coasterIds = $this->cache->get($this->prefix . 'index') ?? [];

        $coasters = [];
        foreach ($coasterIds as $id) {
            $coaster = $this->getById($id);
            if ($coaster) {
                $coasters[] = $coaster;
            }
        }

        return $coasters;
    }

    public function save(Coaster $coaster): void
    {
        $data = [
            'id' => $coaster->getId(),
            'staffCount' => $coaster->getStaffCount(),
            'dailyClients' => $coaster->getDailyClients(),
            'trackLength' => $coaster->getTrackLength(),
            'operatingHoursFrom' => $coaster->getOperatingHours()->getFrom(),
            'operatingHoursTo' => $coaster->getOperatingHours()->getTo(),
        ];

        $this->cache->save($this->prefix . $coaster->getId(), $data);

        $coasterIds = $this->cache->get($this->prefix . 'index') ?? [];
        if (!in_array($coaster->getId(), $coasterIds)) {
            $coasterIds[] = $coaster->getId();
            $this->cache->save($this->prefix . 'index', $coasterIds);
        }
    }

    public function delete(string $id): void
    {
        $this->cache->delete($this->prefix . $id);

        $coasterIds = $this->cache->get($this->prefix . 'index') ?? [];
        $coasterIds = array_filter($coasterIds, function($coasterId) use ($id) {
            return $coasterId !== $id;
        });
        $this->cache->save($this->prefix . 'index', $coasterIds);
    }

    private function hydrateCoaster(array $data): Coaster
    {
        $operatingHours = new OperatingHours(
            $data['operatingHoursFrom'],
            $data['operatingHoursTo']
        );

        $coaster = new Coaster(
            $data['id'],
            $data['staffCount'],
            $data['dailyClients'],
            $data['trackLength'],
            $operatingHours
        );

        $wagons = $this->wagonRepository->getByCoasterId($data['id']);
        foreach ($wagons as $wagon) {
            $coaster->addWagon($wagon);
        }

        $coaster->releaseEvents();

        return $coaster;
    }
}
