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
            $table->boolean('is_student_attended')->nullable()->after('master_rated_at');
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
            $table->dropColumn('is_student_attended');
        });
    }
};
