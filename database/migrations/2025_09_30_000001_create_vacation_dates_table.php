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
        Schema::create('vacation-dates', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name');
            $table->timestamps();
        });

        // Insert provided vacation dates
        DB::table('vacation-dates')->insert([
            ['date' => '2025-01-01', 'name' => 'new year', 'created_at' => now(), 'updated_at' => now()],
            ['date' => '2025-04-10', 'name' => 'easter', 'created_at' => now(), 'updated_at' => now()],
            ['date' => '2025-07-14', 'name' => 'national holiday france', 'created_at' => now(), 'updated_at' => now()],
            ['date' => '2025-05-01', 'name' => 'workers day', 'created_at' => now(), 'updated_at' => now()],
            ['date' => '2025-08-15', 'name' => 'assumption of the virgin mary', 'created_at' => now(), 'updated_at' => now()],
            ['date' => '2025-11-22', 'name' => 'independaceday', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacation-dates');
    }
};
