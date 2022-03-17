<?php

namespace Modules\Workflow\Builders\Configurators;

use Modules\Workflow\Builders\TransitionBuilder;
use Modules\Workflow\Domain\Transition\TransitionGuard;
use Modules\Workflow\Domain\TransitionLog\TransitionsLogRepositoryInterface;

final class TransitionsConfigurator
{
    /**
     * @var TransitionBuilder[]
     */
    private $transitions = [];
    private $metadata;

    public function __construct()
    {
        $this->metadata = new \SplObjectStorage();
    }

    /**
     * @param string $name
     * @param string|string[] $from
     * @param string|string[] $to
     * @return self
     */
    public function add(string $name, $from, $to): self
    {
        $from = is_array($from) ? $from : [$from];

        $transitions = [];
        foreach($from as $f){
            $transitionBuilder = new TransitionBuilder($name, $f, $to);
            $transitions[] = $transitionBuilder;
            $this->transitions[] = $transitionBuilder;
        }

        return $this->replicate($transitions);
    }

    private function replicate(array $transitions): self
    {
        $self = new self();
        $self->transitions = $transitions;
        $self->metadata = $this->metadata;

        return $self;
    }

    /**
     * @param TransitionGuard|TransitionGuard[]|callable|callable[] $guards
     * @return $this
     */
    public function protectBy($guards): self
    {
        array_walk($this->transitions, static function(TransitionBuilder $transition) use ($guards){
            $transition->protectBy($guards);
        });

        return $this;
    }

    /**
     * @param array|null $metadata
     * @return \SplObjectStorage|void
     */
    public function metadata(array $metadata = null)
    {
        if(is_null($metadata)){
            return $this->metadata;
        } else {
            foreach ($this->transitions as $transition) {
                $this->metadata->attach($transition, $metadata);
            }
        }
    }

    /**
     * @return TransitionBuilder[]
     */
    public function transitions(): array
    {
        return array_values($this->transitions);
    }
}