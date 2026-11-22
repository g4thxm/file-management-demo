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
        Schema::create('qcs', function (Blueprint $table) {
            $table->id();
            $table->integer('qc_type');
            $table->integer('grn_id');
            $table->integer('item_id');
            $table->string('batch_no',255);

            $table->double('qty', 10, 2);
            $table->double('scanned_qty', 10, 2);
            $table->double('rejected_qty', 10, 2);
            $table->double('pending_qty', 10, 2);
            $table->integer('status');
            $table->integer('user_id')->nullable(); ;
            $table->datetime('scan_time')->nullable();
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
        Schema::dropIfExists('qcs');
    }
};
