<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // employee_attachments: image to longText if exists
        if (Schema::hasTable('employee_attachments') && Schema::hasColumn('employee_attachments', 'image')) {
            Schema::table('employee_attachments', function (Blueprint $table) {
                $table->longText('image')->change();
            });
        }
        // sick_leaves: attachment to longText if exists
        if (Schema::hasTable('sick_leaves') && Schema::hasColumn('sick_leaves', 'attachment')) {
            Schema::table('sick_leaves', function (Blueprint $table) {
                $table->longText('attachment')->nullable()->change();
            });
        }
        // employees: image to longText if exists
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'image')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->longText('image')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // employee_attachments: image back to string
        if (Schema::hasTable('employee_attachments') && Schema::hasColumn('employee_attachments', 'image')) {
            Schema::table('employee_attachments', function (Blueprint $table) {
                $table->string('image')->change();
            });
        }
        // sick_leaves: attachment back to string
        if (Schema::hasTable('sick_leaves') && Schema::hasColumn('sick_leaves', 'attachment')) {
            Schema::table('sick_leaves', function (Blueprint $table) {
                $table->string('attachment')->nullable()->change();
            });
        }
        // employees: image back to string
        if (Schema::hasTable('employees') && Schema::hasColumn('employees', 'image')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('image')->nullable()->change();
            });
        }
    }
};
