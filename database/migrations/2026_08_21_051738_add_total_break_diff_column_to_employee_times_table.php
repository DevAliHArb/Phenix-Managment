<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('employee_times', 'total_break_diff')) {
                Schema::table('employee_times', function (Blueprint $table) {
                $table->string('total_break_diff')->default('00:00:00')->after('total_time');
                });
            }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('employee_times', 'total_break_diff')) {
            Schema::table('employee_times', function (Blueprint $table) {
            $table->dropColumn('total_break_diff');});
        }
    }
};
