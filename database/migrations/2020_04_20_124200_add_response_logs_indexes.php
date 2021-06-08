<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddResponseLogsIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('response_logs', function (Blueprint $table) {
            $table->index(['user_id', 'request_id', 'http_status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('response_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'request_id', 'http_status']);
        });
    }
}
