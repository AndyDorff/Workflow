<?php

namespace Modules\Workflow\Repositories;

use Modules\Workflow\Adapters\SymfonyWorkflowAdapter;
use Modules\Workflow\Domain\Entity\EntityType;
use Modules\Workflow\Domain\Workflow\Workflow;
use Modules\Workflow\Domain\Workflow\WorkflowId;
use Modules\Workflow\Domain\Workflow\WorkflowInterface;
use Modules\Workflow\Domain\Workflow\WorkflowsRepositoryInterface;
use Symfony\Component\Workflow\Registry;

class SymfonyWorkflowsRepository implements WorkflowsRepositoryInterface
{
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function find(WorkflowId $id): ?WorkflowInterface
    {
        $workflow = $this->registry->get($id->entityType()->toSubject(), $id->name());

        return (new SymfonyWorkflowAdapter($id, $workflow));
    }

    public function findByEntity(EntityType $entityType): ?WorkflowInterface
    {
        return $this->registry->all($entityType->toSubject());
    }

    public function all(): array
    {
        $workflowsProperty = new \ReflectionProperty($this->registry, 'workflows');
        $workflowsProperty->setAccessible(true);
        $workflows = $workflowsProperty->getValue();
        $workflowsProperty->setAccessible(false);

        return $workflows;
    }

    public function save(Workflow $workflow): void
    {
        // TODO: Implement save() method.
    }

    public function delete(Workflow $workflow): void
    {
        // TODO: Implement delete() method.
    }
}