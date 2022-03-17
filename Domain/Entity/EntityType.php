<?php

namespace Modules\Workflow\Domain\Entity;

use Modules\Core\Entities\BaseTypes\BaseType;

final class EntityType extends BaseType
{
    protected function doValidate($value): bool
    {
        return (class_exists($value));
    }

    /**
     * @return object
     * @throws \ReflectionException
     */
    public function toSubject(): object
    {
        return (new \ReflectionClass($this->value()))->newInstanceWithoutConstructor();
    }
}