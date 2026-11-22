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
        Schema::create('grn_subs', function (Blueprint $table) {
            $table->id();
            $table->integer('grn_id');
            $table->integer('item_id');
            $table->string('batch_no',255);
            $table->string('lot_no',255);
            $table->string('shelflife',255)->default(0);
            $table->double('qty', 10, 4)->default(0.0000);
            $table->double('netweight', 10, 4)->default(0.0000);
            $table->datetime('dom')->nullable();      
            $table->datetime('bestbefore')->nullable();      
            $table->string('spq',255);
            $table->string('barcode_no',255);

            // $table->double('grossweight', 10, 4);
            $table->double('scanned_qty', 10, 4)->default(0.0000);
            $table->double('rejected_qty', 10, 4)->default(0.0000);
            $table->integer('grn_status');
            $table->integer('status');
          
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
        Schema::dropIfExists('grn_subs');
    }
};
