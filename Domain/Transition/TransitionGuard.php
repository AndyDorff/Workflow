<?php

namespace Modules\Workflow\Domain\Transition;

use Modules\Core\Entities\AbstractValueObject;
use Modules\Core\Interfaces\ObjectInterface;
use Modules\Workflow\Domain\Workflow\WorkflowEntityInterface;

abstract class TransitionGuard extends AbstractValueObject
{
    /**
     * @param WorkflowEntityInterface $subject
     * @param array $context
     * @return array
     */
    final public function isBlocked(WorkflowEntityInterface $subject, array $context)
    {
        $checkResult = $this->checkGrants($subject, $context);

        return ( is_array($checkResult)
            ? [!$checkResult[0], $checkResult[1] ?? '']
            : [!$checkResult, '']
        );
    }

    /**
     * @param WorkflowEntityInterface $subject
     * @param array $context
     * @return bool|array
     */
    abstract protected function checkGrants(WorkflowEntityInterface $subject, array $context);

    public function equals(ObjectInterface $object): bool
    {
        return (spl_object_id($this) === spl_object_id($object));
    }
}