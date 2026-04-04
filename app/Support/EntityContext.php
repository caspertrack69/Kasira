<?php

namespace App\Support;

use App\Models\Entity;

class EntityContext
{
    private ?Entity $entity = null;

    public function setEntity(?Entity $entity): void
    {
        $this->entity = $entity;
    }

    public function entity(): ?Entity
    {
        return $this->entity;
    }

    public function id(): ?string
    {
        return $this->entity?->getKey();
    }

    public function hasEntity(): bool
    {
        return $this->entity !== null;
    }

    public function clear(): void
    {
        $this->entity = null;
    }
}
