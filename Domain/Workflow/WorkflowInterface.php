<?php

namespace Modules\Workflow\Domain\Workflow;

use Modules\Core\Entities\AbstractIdentity;
use Modules\Core\Entities\SpecialTypes\Status\StatusCode;
use Modules\Workflow\Domain\TransitionLog\TransitionLog;
use Symfony\Component\Workflow\Dumper\DumperInterface;

interface WorkflowInterface
{
    /**
     * @return WorkflowId
     */
    public function id(): AbstractIdentity;
    public function name(): string;
    public function definition(): WorkflowDefinition;
    public function lastTransition(bool $extract = false): ?TransitionLog;

    public function can(WorkflowEntityInterface $subject, string $transitionName, array $context = []): bool;
    public function proceed(WorkflowEntityInterface $subject, string $transitionName, array $context = []): void;

    /**
     * @param DumperInterface|null $dumper
     * @param string|StatusCode $status
     * @param array $options
     * @return string
     */
    public function dump(DumperInterface $dumper = null, $status = null, array $options = []):string;
}