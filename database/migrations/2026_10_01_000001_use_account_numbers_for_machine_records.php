<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // emp_id contains the machine's account number, not an employees.id.
        // Keep unmatched punches available until their employee account is entered.
        Schema::table('machine_records', function (Blueprint $table) {
            $table->dropForeign(['emp_id']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->index('acc_number');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['acc_number']);
        });

        Schema::table('machine_records', function (Blueprint $table) {
            $table->foreign('emp_id')->references('id')->on('employees');
        });
    }
};
