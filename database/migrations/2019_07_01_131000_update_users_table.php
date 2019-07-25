<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Two-Factor Authentication Columns...
            $table->string('phone_country_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('two_factor_options')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone_country_code',
                'phone_number',
                'two_factor_options',
            ]);
        });
    }
}
