<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('employees', 'lookup_employee_type_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->unsignedBigInteger('lookup_employee_type_id')->nullable()->after('position_id');
                $table->foreign('lookup_employee_type_id')->references('id')->on('lookup');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('employees', 'lookup_employee_type_id')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropForeign(['lookup_employee_type_id']);
                $table->dropColumn('lookup_employee_type_id');
            });
        }
    }
};
