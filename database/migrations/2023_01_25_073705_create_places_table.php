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
        Schema::create('places', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->string('category'); // state, country,
            $table->string('city')->nullable();
            $table->unsignedMediumInteger('state_id');
            $table->unsignedBigInteger('country_id');

            $table->foreign('state_id')
                ->references('id')
                ->on('states')
                ->cascadeOnDelete();

            $table->foreign('country_id')
                ->references('id')
                ->on('countries')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('places');
    }
};
