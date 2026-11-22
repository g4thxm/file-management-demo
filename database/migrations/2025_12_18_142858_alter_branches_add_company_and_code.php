<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('branches', function (Blueprint $table) {

            if (!Schema::hasColumn('branches', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->foreign('company_id')
                      ->references('id')
                      ->on('companies')
                      ->onDelete('restrict');
            }

            if (!Schema::hasColumn('branches', 'code')) {
                $table->string('code', 10)->nullable()->after('company_id');
            }
        });
    }

    public function down()
    {
        Schema::table('branches', function (Blueprint $table) {
            if (Schema::hasColumn('branches', 'company_id')) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            }

            if (Schema::hasColumn('branches', 'code')) {
                $table->dropColumn('code');
            }
        });
    }
};
