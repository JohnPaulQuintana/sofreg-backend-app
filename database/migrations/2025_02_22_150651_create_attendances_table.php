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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 10);
            $table->date('date');
            $table->time('clock_in');
            $table->time('clock_out')->nullable();
            $table->string('status');
            $table->string('note')->nullable();
            $table->text('captured_image')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('employee_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
};
