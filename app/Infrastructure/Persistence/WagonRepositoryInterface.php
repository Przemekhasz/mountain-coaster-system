<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\Wagon;

interface WagonRepositoryInterface
{
    public function getById(string $id): ?Wagon;
    public function getByCoasterId(string $coasterId): array;
    public function save(Wagon $wagon): void;
    public function delete(string $id): void;
}
