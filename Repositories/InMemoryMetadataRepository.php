<?php

namespace Modules\Workflow\Repositories;

use Modules\Core\Entities\SpecialTypes\Status\StatusCode;
use Modules\Workflow\Domain\MetadataRepositoryInterface;
use Modules\Workflow\Domain\Transition\Transition;

class InMemoryMetadataRepository implements MetadataRepositoryInterface
{
    /**
     * @var array
     */
    private $workflowMetadata;
    /**
     * @var array
     */
    private $statusesMetadata;
    /**
     * @var \SplObjectStorage|null
     */
    private $transitionsMetadata;

    public function __construct(
        array $workflowMetadata = [],
        array $statusesMetadata = [],
        \SplObjectStorage $transitionsMetadata = null
    ){
        $this->workflowMetadata = $workflowMetadata;
        $this->statusesMetadata = $statusesMetadata;
        $this->transitionsMetadata = $transitionsMetadata ?? new \SplObjectStorage();
    }

    public function getWorkflowMetadata(): array
    {
        return $this->workflowMetadata;
    }

    public function getAllStatusesMetadata(): array
    {
        return $this->statusesMetadata;
    }

    public function getAllTransitionsMetadata(): \SplObjectStorage
    {
        return $this->transitionsMetadata;
    }

    public function getStatusMetadata(StatusCode $status): ?array
    {
        return $this->statusesMetadata[$status->__toString()] ?? null;
    }

    public function getTransitionMetadata(Transition $transition): ?array
    {
        return $this->transitionsMetadata[$transition] ?? null;
    }
}