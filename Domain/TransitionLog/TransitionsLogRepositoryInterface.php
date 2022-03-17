<?php

namespace Modules\Workflow\Domain\TransitionLog;

use Modules\Core\Interfaces\Specification\SpecificationInterface;
use Modules\Workflow\Domain\Entity\Entity;
use Modules\Workflow\Domain\Entity\EntityType;

interface TransitionsLogRepositoryInterface
{
    public function find(TransitionLogId $id): ?TransitionLog;
    public function getByEntity(Entity $entity, ?string $workflowName = null, ?SpecificationInterface $spec = null): array;
    public function getByEntityType(EntityType $entityType, ?string $workflowName = null);

    public function save(TransitionLog $transition): void;
}