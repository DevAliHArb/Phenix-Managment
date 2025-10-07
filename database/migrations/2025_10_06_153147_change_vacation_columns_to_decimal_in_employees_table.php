<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Check if columns exist and change their types to decimal(10,2)
            if (Schema::hasColumn('employees', 'yearly_vacations_used')) {
                $table->decimal('yearly_vacations_used', 10, 2)->default(0.00)->change();
            }
            
            if (Schema::hasColumn('employees', 'yearly_vacations_left')) {
                $table->decimal('yearly_vacations_left', 10, 2)->default(0.00)->change();
            }
            
            if (Schema::hasColumn('employees', 'sick_leave_used')) {
                $table->decimal('sick_leave_used', 10, 2)->default(0.00)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Revert back to integer types
            if (Schema::hasColumn('employees', 'yearly_vacations_used')) {
                $table->integer('yearly_vacations_used')->default(0)->change();
            }
            
            if (Schema::hasColumn('employees', 'yearly_vacations_left')) {
                $table->integer('yearly_vacations_left')->default(0)->change();
            }
            
            if (Schema::hasColumn('employees', 'sick_leave_used')) {
                $table->integer('sick_leave_used')->default(0)->change();
            }
        });
    }
};
