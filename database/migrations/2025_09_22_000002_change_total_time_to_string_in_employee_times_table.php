<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('employee_times', 'total_time')) {
            Schema::table('employee_times', function (Blueprint $table) {
                $table->string('total_time')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('employee_times', 'total_time')) {
            Schema::table('employee_times', function (Blueprint $table) {
                $table->integer('total_time')->nullable()->change();
            });
        }
    }
};
