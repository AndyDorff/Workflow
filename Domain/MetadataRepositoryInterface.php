<?php

namespace Modules\Workflow\Domain;

use Modules\Core\Entities\SpecialTypes\Status\StatusCode;
use Modules\Workflow\Domain\Transition\Transition;

interface MetadataRepositoryInterface
{
    public function getWorkflowMetadata(): array;
    public function getAllStatusesMetadata(): array;
    public function getAllTransitionsMetadata(): \SplObjectStorage;

    public function getStatusMetadata(StatusCode $status): ?array;
    public function getTransitionMetadata(Transition $transition): ?array;
}