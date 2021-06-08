<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url', 4096);
            $table->string('query_params', 4096);
            $table->string('method');
            $table->string('source_ip');
            $table->string('request_id');
            $table->text('raw_request');
            if (DB::connection()->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
                $table->timestamp('microtime', 6)->nullable();
            } else {
                $table->timestamp('microtime', 6)->default(DB::raw('CURRENT_TIMESTAMP(6)'));
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_logs');
    }
}
