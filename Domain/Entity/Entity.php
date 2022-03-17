<?php

namespace Modules\Workflow\Domain\Entity;

use Modules\Core\Entities\AbstractEntity;

final class Entity extends AbstractEntity
{
    public function __construct(EntityId $id, EntityType $type)
    {
        parent::__construct($id);
        $this->state('type', $type);
    }

    public function type(): EntityType
    {
        return $this->state('type');
    }
}