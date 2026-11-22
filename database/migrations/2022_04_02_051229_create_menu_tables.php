<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('_menu', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title');
            $table->string('link');
            $table->string('icon')->nullable()->default(null);
            $table->integer('listing_order')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('_submenu', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('title');
            $table->string('link');
            $table->integer('menu_id')->unsigned();
            $table->integer('listing_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('menu_id')->references('id')->on('_menu')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('_submenu', function (Blueprint $table) {
            $table->dropForeign(['menu_id']);
        });
        Schema::dropIfExists('_submenu');
        Schema::dropIfExists('_menu');
    }
}
