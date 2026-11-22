<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('file_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            // The existing controller uses employee IDs, not users IDs.
            $table->foreignId('entry_user_id')->constrained('employees');
            $table->foreignId('entry_branch_id')->constrained('branches');
            $table->foreignId('entry_department_id')->constrained('departments');
            // Closing a file intentionally records no destination holder.
            $table->foreignId('to_user_id')->nullable()->constrained('employees');
            $table->foreignId('to_branch_id')->nullable()->constrained('branches');
            $table->foreignId('to_department_id')->nullable()->constrained('departments');
            $table->integer('accessibility')->default(0);
            $table->string('subject')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('status')->default(0);
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('file_movements');
    }
};
