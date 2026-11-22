<?php

use Illuminate\Database\Migrations\Migration;

// file_id and its foreign key are already created by the preceding migration.
// Keep this historical migration filename, but do not add/drop the same field twice.
return new class extends Migration {
    public function up(): void {}
    public function down(): void {}
};
