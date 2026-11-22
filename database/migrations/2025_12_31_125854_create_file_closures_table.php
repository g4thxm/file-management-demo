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
        Schema::create('file_closures', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('file_id');
            $table->unsignedBigInteger('closed_by');
            
            $table->date('closed_date');
            $table->time('closed_time');
            $table->text('remarks')->nullable();
            
            $table->timestamps();

            $table->foreign('file_id')->references('id')->on('files');
            $table->foreign('closed_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_closures');
    }
};
