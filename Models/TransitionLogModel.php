<?php

namespace Modules\Workflow\Models;

use Illuminate\Database\Eloquent\Model;

class TransitionLogModel extends Model
{
    protected $table = 'workflow_transitions_log';

    public const UPDATED_AT = null;
    public const CREATED_AT = 'timestamp';

    protected $guarded = ['id'];
    protected $casts = [
        'context' => 'array'
    ];
}