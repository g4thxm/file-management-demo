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
        Schema::create('rejection_scans', function (Blueprint $table) {
            $table->id();
            $table->string('barcode',255);
            $table->integer('grn_id');
            $table->integer('bin_code');
            $table->double('rejected_qty', 10, 4)->default(0.0000);
            $table->integer('user_id');
            $table->string('batch_no',255);
            $table->integer('item_id');
            $table->double('netweight', 10, 4)->default(0.0000);
            $table->datetime('scan_time');   
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
        Schema::dropIfExists('rejection_scans');
    }
};
