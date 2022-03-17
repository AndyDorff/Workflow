<?php

namespace Modules\Workflow\Domain\Workflow;

use Modules\Core\Entities\AbstractValueObject;
use Modules\Core\Entities\SpecialTypes\Status\StatusCode;
use Modules\Core\Interfaces\ObjectInterface;
use Modules\Workflow\Domain\MetadataRepositoryInterface;
use Modules\Workflow\Domain\Transition\Transition;
use Modules\Workflow\Repositories\InMemoryMetadataRepository;
use Webmozart\Assert\Assert;

final class WorkflowDefinition extends AbstractValueObject
{
    public function __construct(
        array      $statuses,
        StatusCode $initialStatus,
        array      $transitions,
        MetadataRepositoryInterface $metadataStore = null
    )
    {
        $metadataStore = $metadataStore ?? new InMemoryMetadataRepository();
        $this
            ->checkStatuses($statuses)
            ->checkInitialStatus($initialStatus, $statuses)
            ->checkTransitions($transitions)
            ->initAttributes(compact( 'statuses', 'initialStatus', 'transitions', 'metadataStore'));
    }

    private function checkStatuses(array $statuses): self
    {
        Assert::allIsInstanceOf($statuses, StatusCode::class);

        return $this;
    }

    private function checkInitialStatus(StatusCode $initialStatus, array $statuses): self
    {
        $contains = false;
        array_walk($statuses, function (StatusCode $statusCode) use ($initialStatus, &$contains) {
            $contains = $contains || $statusCode->equals($initialStatus);
        });

        Assert::true($contains);

        return $this;
    }

    private function checkTransitions(array $transitions): self
    {
        Assert::allIsInstanceOf($transitions, Transition::class);

        return $this;
    }

    public function statuses(): array
    {
        return $this->attribute('statuses');
    }

    public function initialStatus(): StatusCode
    {
        return $this->attribute('initialStatus');
    }

    public function transitions(): array
    {
        return $this->attribute('transitions');
    }

    public function metadataStore(): MetadataRepositoryInterface
    {
        return $this->attribute('metadataStore');
    }

    public function equals(ObjectInterface $object): bool
    {
        return (
            $object instanceof self
            && $this->attribute()->equals($object->attribute())
        );
    }
}