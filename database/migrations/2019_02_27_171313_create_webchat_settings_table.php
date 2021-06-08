<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebchatSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webchat_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name')->default('');
            $table->string('value')->nullable();
            $table->string('type')->default('string');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webchat_settings');
    }
}
