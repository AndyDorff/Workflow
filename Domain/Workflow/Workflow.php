<?php

namespace Modules\Workflow\Domain\Workflow;

use Modules\Core\Entities\AbstractEntity;
use Modules\Core\Entities\BaseTypes\StringType;
use Modules\Core\Entities\SpecialTypes\Status\StatusCode;
use Modules\Workflow\Domain\Entity\Entity;
use Modules\Workflow\Domain\Entity\EntityId;
use Modules\Workflow\Domain\TransitionLog\TransitionLog;
use Modules\Workflow\Domain\TransitionLog\TransitionLogId;
use Modules\Workflow\Mappers\SymfonyTransitionMapper;
use Modules\Workflow\Mappers\SymfonyWorkflowMapper;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Workflow\Dumper\DumperInterface;
use Symfony\Component\Workflow\Dumper\PlantUmlDumper;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\Marking;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method WorkflowId id()
 */
final class Workflow extends AbstractEntity implements WorkflowInterface
{
    private $workflowEngine;
    private $lastTransition;

    public function __construct(WorkflowId $id, WorkflowDefinition $definition, string $name = 'unnamed')
    {
        $this->initState([
            'name' => $name,
            'definition' => $definition
        ]);
        parent::__construct($id);

        $this->workflowEngine = SymfonyWorkflowMapper::getInstance()->reverseMap(
            $this,
            $this->getEventDispatcher()
        );
    }

    public function name(): string
    {
        return $this->state('name');
    }

    public function definition(): WorkflowDefinition
    {
        return $this->state('definition');
    }

    public function lastTransition(bool $extract = false): ?TransitionLog
    {
        $lastTransition = $this->lastTransition;
        if($extract){
            $this->lastTransition = null;
        }

        return $lastTransition;
    }

    public function can(WorkflowEntityInterface $subject, string $transitionName, array $context = []): bool
    {
        return $this->workflowEngine->can($subject, $transitionName);
    }

    public function proceed(WorkflowEntityInterface $subject, string $transitionName, array $context = []): void
    {
        $this->workflowEngine->apply($subject, $transitionName, $context);
    }

    private function getEventDispatcher(): EventDispatcherInterface
    {
        $dispatcher = new EventDispatcher();

        $dispatcher->addListener('workflow.leave', function(Event $event) {
            /**
             * @var WorkflowEntityInterface $subject
             */
            $subject = $event->getSubject();
            $this->lastTransition = new TransitionLog(
                TransitionLogId::create(),
                $this->name(),
                new Entity(new EntityId(new StringType($subject->getWFEntityId())), $this->id()->entityType()),
                StatusCode::fromString(implode(',', array_keys($event->getMarking()->getPlaces()))),
                StatusCode::fromString(implode(',', $event->getTransition()->getTos())),
                $event->getContext()['log'] ?? []
            );
        });

        $dispatcher->addListener('workflow.guard', function(GuardEvent $event) {
            $transition = SymfonyTransitionMapper::getInstance()->map(
                $event->getTransition(),
                $event->getWorkflow()->getMetadataStore()->getTransitionMetadata($event->getTransition())
            );
            foreach ($transition->guards() as $guard){
                [$blocking, $message] = $guard->isBlocked($event->getSubject(), $event->getContext());
                if($blocking){
                    $event->setBlocked(true, $message);
                }
            }
        });

        return $dispatcher;
    }

    public function dump(DumperInterface $dumper = null, $status = null, array $options = []): string
    {
        $dumper = $dumper ?? new PlantUmlDumper(PlantUmlDumper::STATEMACHINE_TRANSITION);
        if($status){
            $status = $status instanceof StatusCode ? strval($status) : $status;
            $marking = new Marking([$status => 1]);
        }

        return $dumper->dump($this->workflowEngine->getDefinition(), $marking ?? null, $options);
    }
}