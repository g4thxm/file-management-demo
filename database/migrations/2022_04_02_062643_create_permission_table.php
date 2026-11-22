<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('permission', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('submenu_id')->unsigned();

            $table->timestamps();
            $table->softDeletes();
            //$table->foreign('user_id')->references('id')->on('_user')->onDelete('restrict');
            //$table->foreign('submenu_id')->references('id')->on('_submenu')->onDelete('restrict');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permission', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['submenu_id']);
        });

        Schema::dropIfExists('permission');
    }
}
