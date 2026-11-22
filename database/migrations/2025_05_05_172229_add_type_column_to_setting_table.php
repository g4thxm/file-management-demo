<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeColumnToSettingTable extends Migration
{
    public function up()
    {
        Schema::table('_setting', function (Blueprint $table) {
            $table->integer('type')->default(1); // Adjust type as needed (e.g., string, enum)
        });
    }

    public function down()
    {
        Schema::table('_setting', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}



