<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveReportByForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $table = 'reports';

        $conn = Schema::getConnection()->getDoctrineSchemaManager();

        $foreignKeys = array_map(function($key) {
            return $key->getName();
        }, $conn->listTableForeignKeys($table));

        if(!in_array('reports_reported_by_foreign', $foreignKeys)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) {
            $table->dropForeign('reports_reported_by_foreign');
        });
    }
}
