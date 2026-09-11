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
        Schema::create('machine_settings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('ip')->nullable();
            $table->string('port')->nullable();
            $table->unsignedInteger('timeout')->default(3);

        });

        DB::table('machine_settings')->insert([
            'ip' => env('ZK_IP'),
            'port' => env('ZK_PORT'),
            'timeout' => env('ZK_TIMEOUT', 3),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_settings');
    }
};
