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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'description',
                'birthdate',
                'gender',
                'charges_enabled',
                'payouts_enabled',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name');
            $table->text('description');
            $table->date('birthdate');
            $table->enum('gender', ['male', 'female']);
            $table->boolean('charges_enabled');
            $table->boolean('payouts_enabled');
        });
    }
};
