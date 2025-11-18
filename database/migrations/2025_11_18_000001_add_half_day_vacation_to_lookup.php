<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $exists = DB::table('lookup')->where('id', 35)->exists();
        if (!$exists) {
            DB::table('lookup')->insert([
                'id' => 35,
                'parent_id' => 30,
                'name' => 'Half day vacation',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('lookup')->where('id', 35)->delete();
    }
};
