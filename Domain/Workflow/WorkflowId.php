<?php

namespace Modules\Workflow\Domain\Workflow;

use Modules\Core\Entities\AbstractIdentity;
use Modules\Core\Entities\BaseTypes\StringType;
use Modules\Core\Interfaces\ObjectInterface;
use Modules\Workflow\Domain\Entity\EntityType;

final class WorkflowId extends AbstractIdentity
{
    public function __construct(StringType $name, EntityType $entityType)
    {
        $this->initAttributes([
            'name' => $name,
            'entityType' => $entityType
        ]);
    }

    public function name(): StringType
    {
        return $this->attribute('name');
    }

    public function entityType(): EntityType
    {
        return $this->attribute('entityType');
    }

    public function equals(ObjectInterface $object): bool
    {
        return (
            ($object instanceof self)
            && $this->name()->equals($object->name())
            && $this->entityType()->equals($object->entityType())
        );
    }
}