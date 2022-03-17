<?php

namespace Modules\Workflow\Domain\TransitionLog;

use Modules\Core\Entities\AbstractEntity;
use Modules\Core\Entities\SpecialTypes\Status\StatusCode;
use Modules\Workflow\Domain\Entity\Entity;
use Modules\Workflow\Domain\Transition\Transition;

final class TransitionLog extends AbstractEntity
{
    public function __construct(
        TransitionLogId $id,
        string $workflowName,
        Entity $entity,
        StatusCode $from,
        StatusCode $to,
        array $context,
        ?\DateTime $timestamp = null
    ) {
        $timestamp = $timestamp ?? new \DateTime();
        $this->initState(compact('id', 'workflowName', 'entity', 'from', 'to', 'context', 'timestamp'));
        parent::__construct($id);
    }

    public function workflowName(): string
    {
        return $this->state('workflowName');
    }

    public function entity(): Entity
    {
        return $this->state('entity');
    }

    public function from(): StatusCode
    {
        return $this->state('from');
    }

    public function to(): StatusCode
    {
        return $this->state('to');
    }

    public function context(): array
    {
        return $this->state('context');
    }

    public function timestamp(): \DateTime
    {
        return $this->state('timestamp');
    }
}