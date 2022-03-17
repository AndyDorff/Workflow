<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransitionsLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workflow_transitions_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('workflow');
            $table->morphs('entity');
            $table->string('from');
            $table->string('to');
            $table->json('context');
            $table->timestamp('timestamp');

            $table->index(['workflow', 'entity_id', 'entity_type'], 'workflow_entity_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workflow_transitions_log');
    }
}
