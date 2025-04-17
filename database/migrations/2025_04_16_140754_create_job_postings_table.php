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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('status');
            $table->string('icon')->nullable()->default('fas fa-palette');
            $table->string('title');
            $table->text('description');
            $table->timestamp('date_posted')->nullable();
            $table->string('address')->default('Quezon City Metro Manila, Philippines');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_postings');
    }
};
