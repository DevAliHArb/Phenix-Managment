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
            if (!Schema::hasColumn('employee_times', 'vacation_type')) {
                $table->string('vacation_type')->nullable()->after('reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_times', function (Blueprint $table) {
            if (Schema::hasColumn('employee_times', 'vacation_type')) {
                $table->dropColumn('vacation_type');
            }
        });
    }
};
