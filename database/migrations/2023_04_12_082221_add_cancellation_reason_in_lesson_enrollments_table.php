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
            $table->string('cancellation_reason')
                ->after('master_cancelled_at')
                ->nullable();

            $table->string('cancellation_remarks', 500)
                ->after('cancellation_reason')
                ->nullable();
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
            $table->dropColumn(['cancellation_reason', 'cancellation_remarks']);
        });
    }
};
