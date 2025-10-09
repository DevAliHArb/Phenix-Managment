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
        // Add the column only if it doesn't exist yet
        if (!Schema::hasColumn('work_schedules', 'vacation_days_per_month')) {
            Schema::table('work_schedules', function (Blueprint $table) {
                $table->decimal('vacation_days_per_month', 10, 2)->default(0)->after('early_leave');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('work_schedules', 'vacation_days_per_month')) {
            Schema::table('work_schedules', function (Blueprint $table) {
                $table->dropColumn('vacation_days_per_month');
            });
        }
    }
};
