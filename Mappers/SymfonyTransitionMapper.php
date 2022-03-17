<?php

namespace Modules\Workflow\Mappers;

use Modules\Core\Entities\SpecialTypes\Status\StatusCode;
use Modules\Workflow\Domain\Transition\Transition;
use Symfony\Component\Workflow\Transition as SymfonyTransition;

final class SymfonyTransitionMapper
{
    private $transitionsMap;
    private static $instance;

    private function __construct()
    {
        $this->transitionsMap = new \SplObjectStorage();
    }

    private function __clone(){}

    public static function getInstance(): self
    {
        return (self::$instance ?? (self::$instance = new self()));
    }

    public function map(SymfonyTransition $symfonyTransition): Transition
    {
        if($this->transitionsMap->contains($symfonyTransition)){
            return $this->transitionsMap[$symfonyTransition];
        }

        $transition = new Transition(
            $symfonyTransition->getName(),
            array_map([StatusCode::class, 'fromString'], $symfonyTransition->getFroms()),
            array_map([StatusCode::class, 'fromString'], $symfonyTransition->getTos())
        );

        $this->transitionsMap[$symfonyTransition] = $transition;

        return $transition;
    }

    public function reverseMap(Transition $transition): SymfonyTransition
    {
        $symfonyTransition = new SymfonyTransition(
            $transition->name(),
            array_map('strval', $transition->from()),
            array_map('strval', $transition->to())
        );

        $this->transitionsMap[$symfonyTransition] = $transition;

        return $symfonyTransition;
    }
}