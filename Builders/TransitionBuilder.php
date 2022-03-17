<?php

namespace Modules\Workflow\Builders;

use Modules\Core\Entities\SpecialTypes\Status\StatusCode;
use Modules\Workflow\Domain\Transition\CallableTransitionGuard;
use Modules\Workflow\Domain\Transition\Transition;
use Modules\Workflow\Domain\Transition\TransitionGuard;

final class TransitionBuilder
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string[]
     */
    private $from;
    /**
     * @var string[]
     */
    private $to;
    /**
     * @var array
     */
    private $guards = [];

    /**
     * @param string $name
     * @param string|string[] $from
     * @param string|string[] $to
     */
    public function __construct(string $name, $from, $to)
    {
        $this->name = $name;
        $this->from = is_array($from) ? $from : [$from];
        $this->to = is_array($to) ? $to : [$to];
    }

    /**
     * @param TransitionGuard|TransitionGuard[]|callable|callable[] $guards
     * @return $this
     */
    public function protectBy($guards): self
    {
        $this->guards = array_map(function($guard){
            if(is_callable($guard)){
                $guard = new CallableTransitionGuard($guard);
            } elseif(!($guard instanceof TransitionGuard)) {
                throw new \InvalidArgumentException('Transition guard should be callable or instance of "'.TransitionGuard::class.'"');
            }

            return $guard;

        }, is_array($guards) ? $guards : [$guards]);

        return $this;
    }

    public function build(): Transition
    {
        $strToStatusCode = static function(string $status) {
            return StatusCode::fromString($status);
        };

        return new Transition(
            $this->name,
            array_map($strToStatusCode, $this->from),
            array_map($strToStatusCode, $this->to),
            $this->guards
        );
    }

}