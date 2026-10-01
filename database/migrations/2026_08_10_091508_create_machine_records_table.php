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
        Schema::create('machine_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_id')->unique();
            $table->unsignedBigInteger('emp_id');
            $table->unsignedBigInteger('login_via');
            $table->unsignedBigInteger('event_type_id');
            $table->timestamp('timestamp');
            $table->unsignedBigInteger('sync_cycle');
            $table->boolean('calculated')->default(false);
            $table->unsignedBigInteger('employee_time_id')->nullable();
            $table->timestamps();

            $table->foreign('login_via')->references('id')->on('lookup');
            $table->foreign('event_type_id')->references('id')->on('lookup');
            $table->foreign('emp_id')->references('id')->on('employees');
            $table->foreign('sync_cycle')->references('id')->on('machine_sync_data');
            $table->foreign('employee_time_id')->references('id')->on('employee_times');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_records');
    }
};
