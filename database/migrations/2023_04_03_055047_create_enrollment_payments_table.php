<?php

use App\Models\LessonEnrollment;
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
        Schema::create('enrollment_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id');
            $table->foreignIdFor(LessonEnrollment::class, 'lesson_enrollment_id')
                ->onDeleteCascade()
                ->index;

            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('aud');

            $table->string('type'); // enrollment or refund
            $table->string('refund_reason')->nullable();

            $table->timestamps();

            $table->index('lesson_enrollment_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('enrollment_payments');
    }
};
