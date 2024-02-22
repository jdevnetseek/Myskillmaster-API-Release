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
        Schema::table('master_lessons', function (Blueprint $table) {
            $table->string('suburb')->after('place_id')->nullable();
            $table->string('postcode')->after('suburb')->nullable();
            $table->string('state')->after('postcode')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('master_lessons', function (Blueprint $table) {
            $table->dropColumn('suburb');
            $table->dropColumn('postcode');
            $table->dropColumn('state');
        });
    }
};
