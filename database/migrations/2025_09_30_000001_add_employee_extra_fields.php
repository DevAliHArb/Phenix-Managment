<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add columns only if they do not exist
        if (!Schema::hasColumn('employees', 'email')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('email')->nullable()->after('phone');
            });
        }
        if (!Schema::hasColumn('employees', 'city')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('city')->nullable()->after('address');
            });
        }
        if (!Schema::hasColumn('employees', 'province')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('province')->nullable()->after('city');
            });
        }
        if (!Schema::hasColumn('employees', 'building_name')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('building_name')->nullable()->after('province');
            });
        }
        if (!Schema::hasColumn('employees', 'floor')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('floor')->nullable()->after('building_name');
            });
        }
        if (!Schema::hasColumn('employees', 'housing_type')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->enum('housing_type', ['rent', 'own'])->nullable()->after('floor');
            });
        }
        if (!Schema::hasColumn('employees', 'owner_name')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('owner_name')->nullable()->after('housing_type');
            });
        }
        if (!Schema::hasColumn('employees', 'owner_mobile_number')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('owner_mobile_number')->nullable()->after('owner_name');
            });
        }
    }

    public function down(): void
    {
        // Drop columns if they exist
        if (Schema::hasColumn('employees', 'email')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }
        if (Schema::hasColumn('employees', 'city')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('city');
            });
        }
        if (Schema::hasColumn('employees', 'province')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('province');
            });
        }
        if (Schema::hasColumn('employees', 'building_name')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('building_name');
            });
        }
        if (Schema::hasColumn('employees', 'floor')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('floor');
            });
        }
        if (Schema::hasColumn('employees', 'housing_type')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('housing_type');
            });
        }
        if (Schema::hasColumn('employees', 'owner_name')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('owner_name');
            });
        }
        if (Schema::hasColumn('employees', 'owner_mobile_number')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropColumn('owner_mobile_number');
            });
        }
    }
};
