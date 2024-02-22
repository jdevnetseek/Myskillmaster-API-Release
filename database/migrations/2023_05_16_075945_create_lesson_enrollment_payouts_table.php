<?php

use App\Models\LessonEnrollment;
use App\Models\UserPayout;
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
        Schema::create('lesson_enrollment_payouts', function (Blueprint $table) {
            $table->foreignIdFor(UserPayout::class)
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignIdFor(LessonEnrollment::class)
                ->constrained()
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
        Schema::dropIfExists('lesson_enrollment_payouts');
    }
};
