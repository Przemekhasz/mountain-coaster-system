<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\Wagon;
use App\Domain\ValueObjects\Speed;
use CodeIgniter\Cache\CacheInterface;

class RedisWagonRepository implements WagonRepositoryInterface
{
    private CacheInterface $cache;
    private string $prefix;
    private string $coasterIndex;

    public function __construct(
        CacheInterface $cache,
        string $environmentPrefix = 'prod'
    ) {
        $this->cache = $cache;
        $this->prefix = $environmentPrefix . '_wagons_';
        $this->coasterIndex = $environmentPrefix . '_coaster_wagons_';
    }

    public function getById(string $id): ?Wagon
    {
        $data = $this->cache->get($this->prefix . $id);

        if (!$data) {
            return null;
        }

        return $this->hydrateWagon($data);
    }

    public function getByCoasterId(string $coasterId): array
    {
        $wagonIds = $this->cache->get($this->coasterIndex . $coasterId) ?? [];

        $wagons = [];
        foreach ($wagonIds as $id) {
            $wagon = $this->getById($id);
            if ($wagon) {
                $wagons[$id] = $wagon;
            }
        }

        return $wagons;
    }

    public function save(Wagon $wagon): void
    {
        $data = [
            'id' => $wagon->getId(),
            'coasterId' => $wagon->getCoasterId(),
            'seatCount' => $wagon->getSeatCount(),
            'speed' => $wagon->getSpeed()->getValue(),
        ];

        $this->cache->save($this->prefix . $wagon->getId(), $data);

        // Update the coaster's wagon index
        $wagonIds = $this->cache->get($this->coasterIndex . $wagon->getCoasterId()) ?? [];
        if (!in_array($wagon->getId(), $wagonIds)) {
            $wagonIds[] = $wagon->getId();
            $this->cache->save($this->coasterIndex . $wagon->getCoasterId(), $wagonIds);
        }
    }

    public function delete(string $id): void
    {
        $wagon = $this->getById($id);
        if (!$wagon) {
            return;
        }

        // Remove from the main wagon store
        $this->cache->delete($this->prefix . $id);

        // Update the coaster's wagon index
        $coasterId = $wagon->getCoasterId();
        $wagonIds = $this->cache->get($this->coasterIndex . $coasterId) ?? [];
        $wagonIds = array_filter($wagonIds, fn($wagonId) => $wagonId !== $id);
        $this->cache->save($this->coasterIndex . $coasterId, $wagonIds);
    }

    private function hydrateWagon(array $data): Wagon
    {
        $speed = new Speed($data['speed']);

        return new Wagon(
            $data['id'],
            $data['coasterId'],
            $data['seatCount'],
            $speed
        );
    }
}
