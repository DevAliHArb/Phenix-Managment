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
        Schema::create('machine_sync_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('last_machine_id')->nullable();
            $table->dateTime('last_attendance_date')->nullable();
            $table->unsignedInteger('added')->nullable();
            $table->unsignedInteger('flagged')->nullable();
            $table->unsignedInteger('ignored')->nullable();
            $table->timestamps();
            $table->string('created_by');
            $table->string('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_sync_data');
    }
};
