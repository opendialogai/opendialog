<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteNonGraphConversationEntities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('conversation_state_logs');
        Schema::dropIfExists('message_templates');
        Schema::dropIfExists('outgoing_intents');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->text('name')->nullable();
            $table->enum('status', ['imported', 'invalid', 'validated', 'published']);
            $table->enum('yaml_validation_status', ['waiting', 'invalid', 'validated']);
            $table->enum('yaml_schema_validation_status', ['waiting', 'invalid', 'validated']);
            $table->enum('scenes_validation_status', ['waiting', 'invalid', 'validated']);
            $table->enum('model_validation_status', ['waiting', 'invalid', 'validated']);
            $table->text('notes')->nullable();
            $table->longText('model');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->string('name', 512)->nullable('false')->unique()->change();
        });

        Schema::create(config('activitylog.table_name'), function (Blueprint $table) {
            $table->increments('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->integer('subject_id')->nullable();
            $table->string('subject_type')->nullable();
            $table->integer('causer_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->text('properties')->nullable();
            $table->timestamps();

            $table->index('log_name');
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->string('graph_uid')->nullable();
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->integer('version_number')->default(0);
        });

        Schema::create('conversation_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('conversation_id');
            $table->text('message');
            $table->string('type');
        });

        Schema::rename('conversation_logs', 'conversation_state_logs');

        Schema::create('outgoing_intents', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
        });

        Schema::create('message_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('outgoing_intent_id');
            $table->string('name');
            $table->mediumText('conditions')->nullable();
            $table->text('message_markup');

            $table->foreign('outgoing_intent_id')
                ->references('id')->on('outgoing_intents')
                ->onDelete('cascade');
        });

        Schema::table('outgoing_intents', function (Blueprint $table) {
            $table->string('name')->unique()->change();
        });

        Schema::table('message_templates', function (Blueprint $table) {
            $table->string('name')->unique()->change();
        });
    }
}
