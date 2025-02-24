<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\Coaster;

interface CoasterRepositoryInterface
{
    public function getById(string $id): ?Coaster;
    public function getAll(): array;
    public function save(Coaster $coaster): void;
    public function delete(string $id): void;
}
