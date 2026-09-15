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
       $lookupData = [
            // Login Method Parent
            ['id' => 36, 'parent_id' => null, 'name' => 'Login Method' , 'code'=>null],
            
            // Login Method Types
            ['id' => 37, 'parent_id' => 36, 'name' => 'Password' , 'code'=>'0'],
            ['id' => 38, 'parent_id' => 36, 'name' => 'Fingerprint' , 'code'=>'1'],
            ['id' => 39, 'parent_id' => 36, 'name' => 'Card','code'=>'2'],
            ['id' => 40, 'parent_id' => 36, 'name' => 'PIN' , 'code'=>'3'],
            ['id' => 41, 'parent_id' => 36, 'name' => 'Face' , 'code'=>'4'],
            ['id' => 42, 'parent_id' => 36, 'name' => 'Undefined' , 'code'=>'255'],
            


             // Event Type Parent
            ['id' => 43, 'parent_id' => null, 'name' => 'Event Type' , 'code'=>null],
            
            // Event Types
            ['id' => 44, 'parent_id' => 43, 'name' => 'Clock In' , 'code'=>'0'],
            ['id' => 45, 'parent_id' => 43, 'name' => 'Clock Out' , 'code'=>'1'],
            ['id' => 46, 'parent_id' => 43, 'name' => 'Break In','code'=>'2'],
            ['id' => 47, 'parent_id' => 43, 'name' => 'Break Out' , 'code'=>'3'],
            ['id' => 48, 'parent_id' => 43, 'name' => 'Overtime In' , 'code'=>'4'],
            ['id' => 49, 'parent_id' => 43, 'name' => 'Overtime Out' , 'code'=>'5'],
            ['id' => 50, 'parent_id' => 43, 'name' => 'Undefined' , 'code'=>'255']
            

        ];

         foreach ($lookupData as $data) {
            // Check if the ID already exists, if not insert the record
            $exists = DB::table('lookup')->where('id', $data['id'])->exists();
            
            if (!$exists) {
                DB::table('lookup')->insert([
                    'id' => $data['id'],
                    'parent_id' => $data['parent_id'],
                    'name' => $data['name'],
                    'code'=> $data['code'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $idsToRemove = [50,49,48,47,46,45,44,43,42,42,40,39,38,37,36];
        
        foreach ($idsToRemove as $id) {
            DB::table('lookup')->where('id', $id)->delete();
        }
    }
};
