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
        Schema::create('grns', function (Blueprint $table) {
            $table->id();
            $table->string('grn_no',255);
            $table->string('po_no',255)->nullable();
            $table->string('invoice_num',255)->nullable();;
            $table->datetime('invoice_date')->nullable();;

            $table->integer('vendor_id');
            $table->integer('location_id');
            
          
            $table->string('grn_type',255);
            $table->string('remark',255)->nullable();;
            $table->datetime('grn_time');
            $table->integer('branch_id');
            $table->integer('status'); 
            $table->datetime('deleted_at')->nullable();      
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
        Schema::dropIfExists('grns');
    }
};
