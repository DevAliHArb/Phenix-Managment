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
        Schema::table('employee_times', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_times', 'flagged')) {
                Schema::table('employee_times', function (Blueprint $table) {
                $table->boolean('flagged')->default(false)->after('total_leave_diff');
                });
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('employee_times', 'flagged')) {
                Schema::table('employee_times', function (Blueprint $table) {
                $table->dropColumn('flagged');
                });
            }
    }
};
