<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePutawaysTable extends Migration
{
    public function up()
    {
        Schema::create('putaways', function (Blueprint $table) {
            $table->id();
            
            // Product details from barcode scan
            $table->string('item_code');
            $table->string('roll_no');
            $table->decimal('quantity', 10, 2);
            $table->text('item_description')->nullable();
            
            // Stock UOM
            $table->string('stock_uom')->nullable();
            
            // Location details from location scan
            $table->string('bin_code');
            $table->string('warehouse')->nullable();
            $table->string('floor')->nullable();
            $table->string('row_no')->nullable();
            $table->string('block')->nullable();
            $table->string('bin_no')->nullable();
            
            // Who scanned it
            $table->string('scanned_by')->nullable();
            
            // Timestamps
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('putaways');
    }
}