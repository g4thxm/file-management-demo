<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();

            $table->string('rfid_code', 100)->unique();
            $table->date('start_date');
            $table->date('due_date');

            $table->string('official_file_number')->nullable();
            $table->string('file_name');
            $table->string('subject');
            $table->text('details')->nullable();
            $table->text('remarks')->nullable();

            $table->string('priority'); 

            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('sub_category_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('employee_id');

            $table->string('upload_file')->nullable();

            $table->integer('status')->default(0);
            $table->unsignedBigInteger('created_by');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('sub_category_id')->references('id')->on('sub_categories');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
