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
            $table->timestamp('master_rated_at')->nullable()->after('master_cancelled_at');
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
            $table->dropColumn('master_rated_at');
        });
    }
};
