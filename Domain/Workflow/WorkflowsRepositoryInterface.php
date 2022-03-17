<?php

namespace Modules\Workflow\Domain\Workflow;

use Modules\Workflow\Domain\Entity\EntityType;

interface WorkflowsRepositoryInterface
{
    public function find(WorkflowId $id): ?WorkflowInterface;
    public function findByEntity(EntityType $entityType): ?WorkflowInterface;
    public function all(): array;

    public function save(Workflow $workflow): void;
    public function delete(Workflow $workflow): void;
}