<?php

namespace Modules\Workflow\Tests\Unit;

use App\Models\Product;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase;
use Modules\Workflow\Builders\Configurators\StatusesConfigurator;
use Modules\Workflow\Builders\Configurators\TransitionsConfigurator;
use Modules\Workflow\Builders\WorkflowBuilder;
use Modules\Workflow\Domain\Workflow\WorkflowEntityInterface;
use Modules\Workflow\Mappers\SymfonyWorkflowMapper;
use Modules\Workflow\Repositories\EloquentTransitionsLogRepository;
use Symfony\Component\Workflow\Dumper\GraphvizDumper;

class BuildWorkflowTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__.'/../../../../bootstrap/app.php';
        if(!defined('LARAVEL_START')){
            define('LARAVEL_START', microtime(true));
        }

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function testBuildWorkflow()
    {
        $subject = new class implements WorkflowEntityInterface {

            private $status = 'draft';

            public function getWFStatus(): ?string
            {
                return $this->status;
            }

            public function setWFStatus(string $status): void
            {
                $this->status = $status;
            }

            public function getWFEntityId()
            {
                return 1;
            }
        };

        $workflow = (new WorkflowBuilder('product_publishing', get_class($subject)))
            ->withStatuses(Product::getAvailableStatuses(), function (StatusesConfigurator $statuses){
                $statuses->initial(Product::STATUS_DRAFT);
            })
            ->withTransitions(function(TransitionsConfigurator $transitions){
                $transitions->add('to_moderate', Product::STATUS_DRAFT, Product::STATUS_MODERATE);
                $transitions->add('approve', Product::STATUS_MODERATE, Product::STATUS_APPROVED);
                $transitions->add('decline', Product::STATUS_MODERATE, Product::STATUS_NOT_APPROVED);
                $transitions->add('upload', Product::STATUS_APPROVED, Product::STATUS_UPLOADED);
                $transitions->add(
                    'delete',
                    [Product::STATUS_DRAFT, Product::STATUS_MODERATE, Product::STATUS_APPROVED, Product::STATUS_NOT_APPROVED],
                    Product::STATUS_DELETED
                );
                $transitions->add(
                    'to_archive',
                    [Product::STATUS_DRAFT, Product::STATUS_MODERATE, Product::STATUS_APPROVED, Product::STATUS_NOT_APPROVED, Product::STATUS_UPLOADED],
                    [Product::STATUS_ARCHIVED]
                );
                $transitions->add(
                    'to_draft',
                    [Product::STATUS_APPROVED, Product::STATUS_NOT_APPROVED, Product::STATUS_UPLOADED],
                    Product::STATUS_DRAFT
                );
            })
            ->build();

        $r = new EloquentTransitionsLogRepository();
        $workflow->proceed($subject, 'to_moderate');
        dd($workflow->lastTransition()->toArray(), $subject);
        $workflow->proceed($subject, 'approve');

        dd((new GraphvizDumper())->dump(SymfonyWorkflowMapper::getInstance()->reverseMap($workflow)->getDefinition()));
    }
}