<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->morphs('likeable');
            $table->unsignedBigInteger('user_id');
            $table->enum('type_id', ['like', 'dislike'])->default('like');
            $table->timestamps();
            $table->softDeletes();

            $table->unique([
                'likeable_id',
                'likeable_type',
                'user_id',
            ], 'likes_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('likes');
    }
}
