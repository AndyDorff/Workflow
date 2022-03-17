<?php

namespace Modules\Workflow\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Entities\BaseTypes\IntegerType;
use Modules\Core\Entities\SpecialTypes\Status\StatusCode;
use Modules\Core\Entities\Specifications\Visitors\EloquentSpecificationVisitor;
use Modules\Core\Interfaces\Specification\SpecificationInterface;
use Modules\Workflow\Domain\Entity\Entity;
use Modules\Workflow\Domain\Entity\EntityId;
use Modules\Workflow\Domain\Entity\EntityType;
use Modules\Workflow\Domain\TransitionLog\TransitionLog;
use Modules\Workflow\Domain\TransitionLog\TransitionLogId;
use Modules\Workflow\Domain\TransitionLog\TransitionLogMapperInterface;
use Modules\Workflow\Domain\TransitionLog\TransitionsLogRepositoryInterface;
use Modules\Workflow\Models\TransitionLogModel;

final class EloquentTransitionsLogRepository implements TransitionsLogRepositoryInterface
{
    private $modelClass;
    private $mapper;

    private $queryScope;

    public function __construct(string $modelClass = null, TransitionLogMapperInterface $mapper = null)
    {
        $this->modelClass = $modelClass ?? TransitionLogModel::class;
        $this->mapper = $mapper;
    }

    public function getByEntity(Entity $entity, ?string $workflowName = null, ?SpecificationInterface $spec = null): array
    {
        $query = $this->query()
            ->when(isset($workflowName), function (Builder $query) use ($workflowName) {
                $query->where('workflow', $workflowName);
            })
            ->where([
                'entity_id' => (string)$entity->id(),
                'entity_type' => $entity->type()->value()
            ]);

        if ($spec) {
            $spec->accept(new EloquentSpecificationVisitor($query));
        }

        $this->resetScope();

        return array_map(
            [$this, 'mapModel'],
            $query->get()->all()
        );
    }

    public function getByEntityType(EntityType $entityType, ?string $workflowName = null)
    {
        $query = $this->query()
            ->when(isset($workflowName), function (Builder $query) use ($workflowName) {
                $query->where('workflow', $workflowName);
            })
            ->where([
                'entity_type' => $entityType->value()
            ]);

        $this->resetScope();

        return array_map(
            [$this, 'mapModel'],
            $query->get()->all()
        );
    }

    public function find(TransitionLogId $id): ?TransitionLog
    {
        $model = $this->query()->find($id->value());
        if ($model) {
            $result = $this->mapModel($model, $id);
        }

        $this->resetScope();

        return $result;
    }

    protected function mapModel(object $model, ?TransitionLogId $id = null): TransitionLog
    {
        $id = $id ?? TransitionLogId::create($model->id);
        $id->setSurrogateId(new IntegerType($model->id));

        if ($this->mapper) {
            return $this->mapper->reverseMap($model, $id);
        } else {
            return new TransitionLog(
                $id,
                $model->workflow,
                new Entity(new EntityId(new IntegerType($model->entity_id)), new EntityType($model->entity_type)),
                StatusCode::fromString($model->old_status),
                StatusCode::fromString($model->new_status),
                $model->context,
                $model->timestamp
            );
        }

    }

    public function save(TransitionLog $transitionLog): void
    {
        $attributes = $this->mapper
            ? $this->mapper->map($transitionLog)
            : [
                'workflow' => $transitionLog->workflowName(),
                'entity_id' => (string)$transitionLog->entity()->id(),
                'entity_type' => (string)$transitionLog->entity()->type(),
                'from' => (string)$transitionLog->from(),
                'to' => (string)$transitionLog->to(),
                'context' => $transitionLog->context(),
                'timestamp' => $transitionLog->timestamp()
            ];

        $this->query(true)->create($attributes);
    }

    public function scopeQuery(callable $queryScope): self
    {
        $this->queryScope = $queryScope;

        return $this;
    }

    public function resetScope(): self
    {
        $this->queryScope = null;

        return $this;
    }

    public function query(bool $noScope = false): Builder
    {
        $query = $this->modelClass::query()->newQuery();

        if($this->queryScope && !$noScope){
            ($this->queryScope)($query);
        }

        return $query;
    }
}