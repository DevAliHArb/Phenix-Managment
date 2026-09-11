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
       if (!Schema::hasColumn('work_schedules', 'break_duration')) {
                Schema::table('work_schedules', function (Blueprint $table) {
                $table->unsignedInteger('break_duration')->default(60)->after('total_hours_per_day');
                });
            }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       if (Schema::hasColumn('work_schedules', 'break_duration')) {
                Schema::table('work_schedules', function (Blueprint $table) {
                $table->dropColumn('break_duration');
                });
            }
    }
};
