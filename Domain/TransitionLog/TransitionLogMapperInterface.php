<?php

namespace Modules\Workflow\Domain\TransitionLog;

interface TransitionLogMapperInterface
{
    public function map(TransitionLog $transition): array;
    public function reverseMap(object $model, TransitionLogId $id): TransitionLog;
}