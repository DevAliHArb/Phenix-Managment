<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // employee_vacations: attachment to longText if exists
        if (Schema::hasTable('employee_vacations') && Schema::hasColumn('employee_vacations', 'attachment')) {
            Schema::table('employee_vacations', function (Blueprint $table) {
                $table->longText('attachment')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // employee_vacations: attachment back to string
        if (Schema::hasTable('employee_vacations') && Schema::hasColumn('employee_vacations', 'attachment')) {
            Schema::table('employee_vacations', function (Blueprint $table) {
                $table->string('attachment')->nullable()->change();
            });
        }
    }
};
