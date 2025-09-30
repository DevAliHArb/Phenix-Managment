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
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->boolean('monday')->default(false);
            $table->boolean('tuesday')->default(false);
            $table->boolean('wednesday')->default(false);
            $table->boolean('thursday')->default(false);
            $table->boolean('friday')->default(false);
            $table->boolean('saturday')->default(false);
            $table->boolean('sunday')->default(false);
            $table->time('start_time');
            $table->time('end_time');
            $table->time('total_hours_per_day');
            $table->integer('late_arrival')->default(0); // in minutes
            $table->integer('early_leave')->default(0); // in minutes
            $table->timestamps();
        });

        // Insert a dummy row
        DB::table('work_schedules')->insert([
            'monday' => true,
            'tuesday' => true,
            'wednesday' => true,
            'thursday' => true,
            'friday' => true,
            'saturday' => false,
            'sunday' => false,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'total_hours_per_day' => '08:00:00',
            'late_arrival' => 10,
            'early_leave' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
