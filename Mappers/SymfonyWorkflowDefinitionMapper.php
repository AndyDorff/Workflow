<?php

namespace Modules\Workflow\Mappers;

use Modules\Core\Entities\SpecialTypes\Status\StatusCode;
use Modules\Workflow\Domain\Transition\Transition;
use Modules\Workflow\Domain\Workflow\WorkflowDefinition;
use Modules\Workflow\Repositories\InMemoryMetadataRepository;
use Symfony\Component\Workflow\Definition as SymfonyDefinition;
use Symfony\Component\Workflow\Metadata\InMemoryMetadataStore;
use Symfony\Component\Workflow\Transition as SymfonyTransition;

final class SymfonyWorkflowDefinitionMapper
{
    private static $instance;

    private function __construct(){}
    private function __clone(){}

    public static function getInstance(): self
    {
        return (self::$instance ?? (self::$instance = new self()));
    }

    public function map(SymfonyDefinition $definition): WorkflowDefinition
    {
        [$statuses, $initialStatus] = $this->mapPlaces($definition->getPlaces(), $definition->getInitialPlaces());
        [$transitions, $metadataStore] = $this->mapTransitions($definition);

        return new WorkflowDefinition(
            $statuses,
            $initialStatus,
            $transitions,
            $metadataStore
        );
    }

    public function reverseMap(WorkflowDefinition $definition): SymfonyDefinition
    {
        [$transitions, $metadataStore] = $this->reverseMapTransitions($definition);

        return new SymfonyDefinition(
            array_map('strval', $definition->statuses()),
            $transitions,
            $definition->initialStatus()->__toString(),
            $metadataStore
        );
    }

    private function mapPlaces(array $places, array $initialPlaces): array
    {
        $statuses = [];
        foreach($places as $place){
            $status = StatusCode::fromString($place);
            if(!isset($initialStatus) && in_array($place, $initialPlaces)){
                $initialStatus = $status;
            }
            $statuses[] = $statuses;
        }

        return [$statuses, $initialStatus ?? null];
    }

    private function mapTransitions(SymfonyDefinition $definition): array
    {
        $transitions = [];
        $metadata = new InMemoryMetadataRepository(
            $definition->getMetadataStore()->getWorkflowMetadata(),
            array_map([$definition->getMetadataStore(), 'getPlaceMetadata'], $definition->getPlaces()),
            array_reduce(
                $definition->getTransitions(),
                static function(\SplObjectStorage $transitionsMetadata, SymfonyTransition $symfonyTransition) use ($definition, &$transitions){
                    $transitions[] = $transition = SymfonyTransitionMapper::getInstance()->map($symfonyTransition);
                    $transitionsMetadata->attach(
                        $transition,
                        $definition->getMetadataStore()->getTransitionMetadata($symfonyTransition)
                    );

                    return $transitionsMetadata;
                },
                new \SplObjectStorage())
        );

        return [$transitions, $metadata];
    }

    private function reverseMapTransitions(WorkflowDefinition $definition): array
    {
        $symfonyTransitions = [];
        $metadata = new InMemoryMetadataStore(
            $definition->metadataStore()->getWorkflowMetadata(),
            $definition->metadataStore()->getAllStatusesMetadata(),
            array_reduce(
                $definition->transitions(),
                static function(\SplObjectStorage $transitionsMetadata, Transition $transition) use ($definition, &$symfonyTransitions){
                    $symfonyTransitions[] = $symfonyTransition = SymfonyTransitionMapper::getInstance()->reverseMap($transition);
                    $transitionsMetadata->attach(
                        $symfonyTransition,
                        $definition->metadataStore()->getTransitionMetadata($transition)
                    );

                    return$transitionsMetadata;
                },
                new \SplObjectStorage()
            )
        );

        return [$symfonyTransitions, $metadata];
    }
}