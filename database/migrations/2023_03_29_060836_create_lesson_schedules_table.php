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
            Schema::create('lesson_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('master_lesson_id')->constrained()->cascadeOnDelete();
                $table->bigInteger('duration_in_hours');
                $table->dateTime('schedule_start');
                $table->dateTime('schedule_end');
                $table->tinyInteger('slots')->default(1);
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
            Schema::dropIfExists('lesson_schedules');
        }
    };
