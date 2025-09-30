<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('salaries', 'start_date')) {
            Schema::table('salaries', function (Blueprint $table) {
                $table->date('start_date')->nullable()->after('status');
            });
        }
        if (!Schema::hasColumn('salaries', 'end_date')) {
            Schema::table('salaries', function (Blueprint $table) {
                $table->date('end_date')->nullable()->after('start_date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('salaries', 'start_date')) {
            Schema::table('salaries', function (Blueprint $table) {
                $table->dropColumn('start_date');
            });
        }
        if (Schema::hasColumn('salaries', 'end_date')) {
            Schema::table('salaries', function (Blueprint $table) {
                $table->dropColumn('end_date');
            });
        }
    }
};
