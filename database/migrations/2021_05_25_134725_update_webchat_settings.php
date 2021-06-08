<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateWebchatSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webchat_settings', function (Blueprint $table) {
            $table->string('description')->nullable();
            $table->string('section')->nullable();
            $table->string('subsection')->nullable();
            $table->string('display_name')->nullable();
            $table->boolean('display')->default(true);
            $table->unsignedInteger('sibling')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('webchat_settings', function (Blueprint $table) {
            $table->removeColumn('description');
            $table->removeColumn('section');
            $table->removeColumn('display_name');
            $table->removeColumn('display');
            $table->removeColumn('sibling');
        });
    }
}
