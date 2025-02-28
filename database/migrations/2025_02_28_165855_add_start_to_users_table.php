<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->time('time_of_duty_start')->default('09:00')->nullable()->after('position');
            $table->time('time_of_duty_end')->default('18:00')->nullable()->after('time_of_duty_start');
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
            $table->dropColumn(['time_of_duty_start', 'time_of_duty_end']);
        });
    }
};
