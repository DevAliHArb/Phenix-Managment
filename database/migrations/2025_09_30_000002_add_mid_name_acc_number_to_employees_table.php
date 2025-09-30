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
        if (!Schema::hasColumn('employees', 'mid_name')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('mid_name')->nullable()->after('name');
            });
        }
        if (!Schema::hasColumn('employees', 'acc_number')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->integer('acc_number')->nullable()->after('mid_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('employees', 'acc_number')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('acc_number');
            });
        }
        if (Schema::hasColumn('employees', 'mid_name')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('mid_name');
            });
        }
    }
};
