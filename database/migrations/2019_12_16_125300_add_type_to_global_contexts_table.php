<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToGlobalContextsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_contexts', function (Blueprint $table) {
            if (DB::connection()->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
                $table->enum('type', ['array', 'boolean', 'float', 'int', 'string', 'timestamp'])->default('');
            } else {
                $table->enum('type', ['array', 'boolean', 'float', 'int', 'string', 'timestamp']);
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
        Schema::table('global_contexts', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
