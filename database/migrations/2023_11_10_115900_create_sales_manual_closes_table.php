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
        Schema::create('sales_manual_closes', function (Blueprint $table) {
            $table->id();
            $table->integer('dispatch_entry_id');
            $table->integer('canceled_user_id');
            $table->timestamps();
            $table->datetime('deleted_at')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_manual_closes');
    }
};
