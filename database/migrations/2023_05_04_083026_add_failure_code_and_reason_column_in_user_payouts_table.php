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
        Schema::table('user_payouts', function (Blueprint $table) {
            $table->string('failure_code')
                ->nullable()
                ->after('is_initiated_by_user');

            $table->string('failure_message', 500)
                ->nullable()
                ->after('failure_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_payouts', function (Blueprint $table) {
            $table->dropColumn(['failure_code', 'failure_message']);
        });
    }
};
