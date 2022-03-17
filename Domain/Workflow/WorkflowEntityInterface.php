<?php

namespace Modules\Workflow\Domain\Workflow;

interface WorkflowEntityInterface
{
    /**
     * @return mixed
     */
    public function getWFEntityId();

    public function getWFStatus(): ?string;
    public function setWFStatus(string $status, array $context = []): void;
}