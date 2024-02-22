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
        Schema::create('enrollment_reschedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enrollment_id');

            $table->unsignedBigInteger('from_schedule_id');
            $table->unsignedBigInteger('to_schedule_id');
            $table->unsignedBigInteger('rescheduled_by');

            $table->string('reason', 100);
            $table->string('remarks', 500)->nullable();

            $table->foreign('enrollment_id')
                ->references('id')
                ->on('lesson_enrollments')
                ->cascadeOnDelete();

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
        Schema::dropIfExists('enrollment_reschedules');
    }
};
