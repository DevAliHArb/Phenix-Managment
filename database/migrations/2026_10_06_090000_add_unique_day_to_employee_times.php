<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Never discard existing attendance to make the unique constraint fit.
        $duplicates = DB::table('employee_times')->whereNotNull('date')
            ->select('employee_id', 'date')->groupBy('employee_id', 'date')
            ->havingRaw('COUNT(*) > 1')->exists();

        if ($duplicates) {
            throw new RuntimeException('Resolve duplicate employee_times employee_id/date rows before running this migration. No attendance rows have been deleted.');
        }

        Schema::table('employee_times', function (Blueprint $table) {
            $table->unique(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('employee_times', function (Blueprint $table) {
            $table->dropUnique(['employee_id', 'date']);
        });
    }
};
