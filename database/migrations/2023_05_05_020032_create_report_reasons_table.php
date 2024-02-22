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
        Schema::create('report_reasons', function (Blueprint $table) {
            $table->unsignedBigInteger('report_id');
            $table->unsignedBigInteger('report_category_id');

            $table->foreign('report_id')
                ->references('id')->on('reports')
                ->onDelete('cascade');

            $table->foreign('report_category_id')
                ->references('id')->on('report_categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_reasons');
    }
};
