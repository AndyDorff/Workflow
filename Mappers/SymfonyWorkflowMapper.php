<?php

namespace Modules\Workflow\Mappers;

use Modules\Workflow\Domain\Workflow\Workflow;
use Modules\Workflow\Domain\Workflow\WorkflowId;
use Modules\Workflow\Domain\Workflow\WorkflowInterface;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Workflow as SymfonyWorkflow;
use Symfony\Component\Workflow\WorkflowInterface as SymfonyWorkflowInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class SymfonyWorkflowMapper
{
    private static $instance;
    private $definitionMapper;

    private function __construct()
    {
        $this->definitionMapper = SymfonyWorkflowDefinitionMapper::getInstance();
    }

    private function __clone(){}

    public static function getInstance(): self
    {
        return (self::$instance ?? (self::$instance = new self()));
    }

    public function map(WorkflowId $id, SymfonyWorkflowInterface $symfonyWorkflow): WorkflowInterface
    {
        return new Workflow(
            $id,
            $this->definitionMapper->map($symfonyWorkflow->getDefinition()),
            $symfonyWorkflow->getName()
        );
    }

    public function reverseMap(WorkflowInterface $workflow, ?EventDispatcherInterface $dispatcher = null): SymfonyWorkflowInterface
    {
        return new SymfonyWorkflow(
            $this->definitionMapper->reverseMap($workflow->definition()),
            new MethodMarkingStore(true, 'WFStatus'),
            $dispatcher,
            $workflow->name()
        );
    }
}