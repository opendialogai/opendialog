<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatbotUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatbot_users', function (Blueprint $table) {
            $table->string('user_id')->unique();
            $table->timestamps();

            $table->string('ip_address');
            $table->string('country');
            $table->string('browser_language');
            $table->string('os');
            $table->string('browser');
            $table->string('timezone');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('platform')->nullable();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->foreign('user_id')->references('user_id')->on('chatbot_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign('messages_user_id_foreign');
        });

        Schema::dropIfExists('chatbot_users');
    }
}
