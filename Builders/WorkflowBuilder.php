<?php

namespace Modules\Workflow\Builders;


use Modules\Core\Entities\BaseTypes\StringType;
use Modules\Core\Entities\SpecialTypes\Status\StatusCode;
use Modules\Workflow\Builders\Configurators\StatusesConfigurator;
use Modules\Workflow\Builders\Configurators\TransitionsConfigurator;
use Modules\Workflow\Domain\Entity\EntityType;
use Modules\Workflow\Domain\Workflow\Workflow;
use Modules\Workflow\Domain\Workflow\WorkflowDefinition;
use Modules\Workflow\Domain\Workflow\WorkflowId;
use Modules\Workflow\Domain\Workflow\WorkflowInterface;
use Modules\Workflow\Repositories\InMemoryMetadataRepository;
use Symfony\Component\Workflow\Metadata\InMemoryMetadataStore;
use Symfony\Component\Workflow\Metadata\MetadataStoreInterface;

final class WorkflowBuilder
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $subjectClass;
    /**
     * @var StatusesConfigurator
     */
    private $statusesConfigurator;
    /**
     * @var TransitionsConfigurator
     */
    private $transitionsConfigurator;
    /**
     * @var callable|null
     */
    private $setupStatuses;
    /**
     * @var callable
     */
    private $setupTransitions;

    public function __construct(string $name, string $subjectClass)
    {
        $this->name = $name;
        $this->subjectClass = $subjectClass;
        $this->statusesConfigurator = new StatusesConfigurator([]);
        $this->transitionsConfigurator = new TransitionsConfigurator();
    }

    public function withStatuses(array $statuses, ?callable $setupStatuses = null): self
    {
        $this->statusesConfigurator = new StatusesConfigurator($statuses);
        $this->setupStatuses = $setupStatuses;

        return $this;
    }

    public function withTransitions(callable $setupTransitions): self
    {
        $this->setupTransitions = $setupTransitions;

        return $this;
    }

    public function build(): WorkflowInterface
    {
        $entityType = new EntityType($this->subjectClass);
        if($this->setupStatuses){
            ($this->setupStatuses)($this->statusesConfigurator);
        }
        if($this->setupTransitions){
            ($this->setupTransitions)($this->transitionsConfigurator);
        }

        $metadata = [
            'statuses' => $this->statusesConfigurator->metadata(),
            'transitions' => new \SplObjectStorage(),
        ];
        $statuses = array_map([StatusCode::class, 'fromString'], $this->statusesConfigurator->statuses());
        $transitions = array_map(function(TransitionBuilder $transitionBuilder) use (&$metadata) {
            $transition = $transitionBuilder->build();
            if($transitionMetadata = ($this->transitionsConfigurator->metadata()[$transitionBuilder] ?? null)){
                $metadata['transitions']->attach($transition, $transitionMetadata);
            }

            return $transition;
        }, $this->transitionsConfigurator->transitions());

        $workflow = new Workflow(
            new WorkflowId(new StringType($this->name), $entityType),
            new WorkflowDefinition(
                $statuses,
                StatusCode::fromString($this->statusesConfigurator->initial()),
                $transitions,
                new InMemoryMetadataRepository([], $metadata['statuses'], $metadata['transitions'])
            ),
            $this->name
        );

        return $workflow;
    }
}