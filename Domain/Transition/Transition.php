<?php

namespace Modules\Workflow\Domain\Transition;

use Modules\Core\Entities\AbstractValueObject;
use Modules\Core\Entities\SpecialTypes\Status\StatusCode;
use Modules\Core\Interfaces\ObjectInterface;
use Modules\Workflow\Domain\MetadataTrait;

final class Transition extends AbstractValueObject
{
    /**
     * @param string $name
     * @param StatusCode|StatusCode[] $from
     * @param StatusCode|StatusCode[] $to
     * @param array $guards
     */
    public function __construct(
        string $name,
        $from,
        $to,
        array $guards = []
    ){
        $to = array_map(function(StatusCode $to) { return $to; }, (array) $to);
        $from = array_map(function(StatusCode $from) { return $from; }, (array) $from);
        $guards = array_map(function(TransitionGuard $guard) { return $guard; }, $guards);

        $this->initAttributes(compact('name','from', 'to', 'guards'));
    }

    public function name(): string
    {
        return $this->attribute('name');
    }

    public function from(): array
    {
        return $this->attribute('from');
    }

    public function to(): array
    {
        return $this->attribute('to');
    }

    /**
     * @return TransitionGuard[]
     */
    public function guards(): array
    {
        return $this->attribute('guards');
    }

    public function equals(ObjectInterface $object): bool
    {
        return (
            ($object instanceof self)
            && $this->name() === $object->name()
            && $this->checkStatusesEquality($this->from(), $object->from())
            && $this->checkStatusesEquality($this->to(), $object->to())
        );
    }

    private function checkStatusesEquality(array $statuses1, array $statuses2): bool
    {
        $sort = static function(StatusCode $s1, StatusCode $s2){
            return strval($s1) <=> strval($s2);
        };

        usort($statuses1, $sort);
        usort($statuses2, $sort);

        return implode(';;', $statuses1) === implode(';;', $statuses2);
    }
}