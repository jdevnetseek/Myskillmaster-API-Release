<?php

use App\Models\MasterLesson;
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
        Schema::create('lesson_enrollments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_code')->unique();

            $table->foreignIdFor(MasterLesson::class, 'lesson_id');

            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('master_id');

            $table->string('to_learn', 500);

            $this->amountRelatedColumns($table);

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('student_cancelled_at')->nullable();
            $table->timestamp('master_cancelled_at')->nullable();

            $table->foreign('student_id')
                ->references('id')
                ->on('users');

                $table->foreign('master_id')
                ->references('id')
                ->on('users');

            $table->timestamps();
        });
    }

    private function amountRelatedColumns(Blueprint $table)
    {
        $table->decimal('lesson_price', 12, 2);
        $table->decimal('sub_total', 12, 2);
        $table->decimal('application_fee_amount', 12, 2);
        $table->decimal('application_fee_rate', 5, 2);
        $table->decimal('grand_total', 12, 2);
        $table->decimal('master_earnings', 12, 2);
        $table->char('currency', 3)->default(config('cashier.currency'));
        $table->decimal('refunded_amount', 12, 2)->nullable();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lesson_enrollments');
    }
};
