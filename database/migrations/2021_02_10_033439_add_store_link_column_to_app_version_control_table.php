<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStoreLinkColumnToAppVersionControlTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_versions', function (Blueprint $table) {
            $table->string('store_link')->nullable()->after('version');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('app_versions', 'store_link')) {
            Schema::table('app_versions', function (Blueprint $table) {
                $table->dropColumn('store_link');
            });
        }

        if (Schema::hasColumn('app_versions', 'store_url')) {
            Schema::table('app_versions', function (Blueprint $table) {
                $table->dropColumn('store_url');
            });
        }

    }
}
