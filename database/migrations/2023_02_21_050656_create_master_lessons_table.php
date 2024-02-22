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
        Schema::create('master_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->nullable()
                ->references('id')
                ->on('categories')
                ->nullOnDelete();

            $table->string('title');
            $table->string('slug');
            $table->string('description', 500)->nullable();
            $table->bigInteger('duration_in_hours');
            $table->decimal('lesson_price', 12, 2);
            $table->string('currency', 5)->default(config('cashier.currency'));
            $table->json('available_days');
            $table->mediumInteger('place_id');
            $table->tinyInteger('is_remote_supported');
            $table->tinyInteger('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_lessons');
    }
};
