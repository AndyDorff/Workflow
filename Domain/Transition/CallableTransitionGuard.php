<?php

namespace Modules\Workflow\Domain\Transition;

use Modules\Workflow\Domain\Workflow\WorkflowEntityInterface;

final class CallableTransitionGuard extends TransitionGuard
{
    private $handler;

    public function __construct(callable $handler)
    {
        $this->handler = $handler;
    }

    protected function checkGrants(WorkflowEntityInterface $subject, array $context)
    {
        return call_user_func($this->handler, $subject, $context);
    }
}