<?php

namespace Modules\Workflow\Domain\TransitionLog;

use Modules\Core\Entities\BaseTypes\BaseIdentity;
use Modules\Core\Entities\BaseTypes\IntegerType;
use Modules\Core\Entities\BaseTypes\NullType;
use Modules\Core\Entities\BaseTypes\StringType;

final class TransitionLogId extends BaseIdentity
{
    public static function create($value = null): self
    {
        $value = is_int($value)
            ? new IntegerType($value)
            : (is_null($value)
                ? new NullType()
                : new StringType((string)$value)
            );

        return new self($value);
    }
}