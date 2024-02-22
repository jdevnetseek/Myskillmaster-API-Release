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
        Schema::table('lesson_enrollments', function (Blueprint $table) {
            $table->unsignedBigInteger('schedule_id')->after('lesson_id');
            $table->index('schedule_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lesson_enrollments', function (Blueprint $table) {
            $table->dropColumn('schedule_id');
        });
    }
};
