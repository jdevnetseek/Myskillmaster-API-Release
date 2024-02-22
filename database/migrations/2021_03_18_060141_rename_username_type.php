<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameUsernameType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('users', 'username_type')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('username_type', 'primary_username');
            });
        }
    }
}
