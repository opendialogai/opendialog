<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncreaseValueColumnSizeForWebchatSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (config('database.default') !== 'testbench' && config('database.default') !== 'sqlite' && config('database.default') !== 'pgsql') {
            DB::statement('ALTER TABLE webchat_settings MODIFY COLUMN value VARCHAR(8192)');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (config('database.default') !== 'testbench' && config('database.default') !== 'pgsql') {
            DB::statement('ALTER TABLE webchat_settings MODIFY COLUMN value VARCHAR(255)');
        }
    }
}
