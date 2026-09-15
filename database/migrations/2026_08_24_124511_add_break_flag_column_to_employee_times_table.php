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
                if (!Schema::hasColumn('employee_times', 'break_flag')) {
                    Schema::table('employee_times', function (Blueprint $table) {
                    $table->string('break_flag')->default('pass')->after('flagged');
                    });
                }
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('employee_times', 'break_flag')) {
                Schema::table('employee_times', function (Blueprint $table) {
                $table->dropColumn('break_flag');
                });
                }
        }
};
