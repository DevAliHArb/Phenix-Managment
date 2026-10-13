<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Legacy MySQL/MariaDB defaults can give the first TIMESTAMP column
        // ON UPDATE CURRENT_TIMESTAMP. Punch times must never change on updates.
        Schema::table('machine_records', function (Blueprint $table) {
            $table->dateTime('timestamp')->change();
        });
    }

    public function down(): void
    {
        // An explicit default avoids restoring the implicit ON UPDATE behavior.
        Schema::table('machine_records', function (Blueprint $table) {
            $table->timestamp('timestamp')->useCurrent()->change();
        });
    }
};
