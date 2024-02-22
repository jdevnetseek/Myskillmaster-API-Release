<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTitleMessageToVersionControll extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_versions', function (Blueprint $table) {
            $table->after('upgrade_guide', function (Blueprint $table) {
                $table->string('title')->nullable();
                $table->string('message', 350)->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_versions', function (Blueprint $table) {
            $table->dropColumn(['title', 'message']);
        });
    }
}
