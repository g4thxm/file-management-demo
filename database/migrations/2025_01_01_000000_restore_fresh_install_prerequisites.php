<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Fresh-install prerequisites absent from the supplied source-review copy.
// Derived from the existing models/controllers. Contains no imported records.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_no')->nullable();
            $table->date('purchase_date')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('_setting', function (Blueprint $table) {
            $table->id();
            $table->string('date_format')->default('d-m-Y');
            $table->string('datetime_format')->default('H:i:s');
            $table->string('label')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            foreach (['front_logo', 'login_logo', 'back_img', 'favi'] as $field) {
                $table->string($field)->nullable();
            }
            $table->integer('darkmode')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('Loc_Name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->integer('user_type')->default(0);
            $table->integer('permission_level')->default(1);
            $table->integer('darkmode')->default(0);
            $table->softDeletes();
        });
    }
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['branch_id', 'location_id', 'user_type', 'permission_level', 'darkmode', 'deleted_at']);
        });
        Schema::dropIfExists('locations');
        Schema::dropIfExists('configurations');
        Schema::dropIfExists('_setting');
        Schema::dropIfExists('purchase_orders');
    }
};
