<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameStoreLink extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('app_versions', 'store_link')) {
            Schema::table('app_versions', function (Blueprint $table) {
                $table->renameColumn('store_link', 'store_url');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('app_versions', 'store_url')) {
            Schema::table('app_versions', function (Blueprint $table) {
                $table->renameColumn('store_url', 'store_link');
            });
        }
    }
}
