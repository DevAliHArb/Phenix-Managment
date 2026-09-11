<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('lookup', 'code')) {
            Schema::table('lookup', function (Blueprint $table) {
                $table->string('code')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('lookup', 'code')) {
            Schema::table('lookup', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        }
    }
};
