_<?php

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
            Schema::table('lesson_schedules', function (Blueprint $table) {
                $table->json('dows')->nullable()->after('schedule_start');
                $table->string('recurrence')->nullable()->after('dows');
                $table->string('frequency')->nullable()->after('recurrence');
                $table->string('period')->nullable()->after('frequency');
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::table('lesson_schedules', function (Blueprint $table) {
                //
            });
        }
    };
