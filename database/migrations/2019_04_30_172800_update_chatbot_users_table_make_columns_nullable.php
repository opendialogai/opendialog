<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateChatbotUsersTableMakeColumnsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chatbot_users', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->change();
            $table->string('country')->nullable()->change();
            $table->string('browser_language')->nullable()->change();
            $table->string('os')->nullable()->change();
            $table->string('browser')->nullable()->change();
            $table->string('timezone')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chatbot_users', function (Blueprint $table) {
            $table->string('ip_address')->nullable(false)->change();
            $table->string('country')->nullable(false)->change();
            $table->string('browser_language')->nullable(false)->change();
            $table->string('os')->nullable(false)->change();
            $table->string('browser')->nullable(false)->change();
            $table->string('timezone')->nullable(false)->change();
        });
    }
}
