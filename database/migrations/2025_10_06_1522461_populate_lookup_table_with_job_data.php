<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $lookupData = [
            // Job Description Parent
            ['id' => 1, 'parent_id' => null, 'name' => 'Job Description'],
            
            // QA Roles (commented out ones from original query are skipped)
            ['id' => 4, 'parent_id' => 1, 'name' => 'QA - Intermediate'],
            ['id' => 5, 'parent_id' => 1, 'name' => 'QA - Senior'],
            
            // Developer Roles
            ['id' => 6, 'parent_id' => 1, 'name' => 'Developer - Intern'],
            ['id' => 7, 'parent_id' => 1, 'name' => 'Developer - Junior'],
            ['id' => 8, 'parent_id' => 1, 'name' => 'Developer - Intermediate'],
            ['id' => 9, 'parent_id' => 1, 'name' => 'Developer - Senior'],
            
            // Designer Roles
            ['id' => 10, 'parent_id' => 1, 'name' => 'Designer - Intern'],
            ['id' => 11, 'parent_id' => 1, 'name' => 'Designer - Junior'],
            ['id' => 12, 'parent_id' => 1, 'name' => 'Designer - Intermediate'],
            ['id' => 13, 'parent_id' => 1, 'name' => 'Designer - Senior'],
            
            // Manager Roles
            ['id' => 14, 'parent_id' => 1, 'name' => 'Manager'],
            
            // Leadership Roles
            ['id' => 17, 'parent_id' => 1, 'name' => 'Team Lead'],
            ['id' => 18, 'parent_id' => 1, 'name' => 'Tech Lead'],
            
            // Scrum Master
            ['id' => 19, 'parent_id' => 1, 'name' => 'Scrum Master'],
            
            // Business Analyst Roles
            ['id' => 20, 'parent_id' => 1, 'name' => 'Business Analyst - Intern'],
            ['id' => 21, 'parent_id' => 1, 'name' => 'Business Analyst - Junior'],
            ['id' => 22, 'parent_id' => 1, 'name' => 'Business Analyst - Intermediate'],
            ['id' => 23, 'parent_id' => 1, 'name' => 'Business Analyst - Senior'],
            
            // Contract Types Parent
            ['id' => 24, 'parent_id' => null, 'name' => 'Contract Types'],
            
            // Contract Types
            ['id' => 25, 'parent_id' => 24, 'name' => 'Full-time'],
            ['id' => 26, 'parent_id' => 24, 'name' => 'Part-time'],
            ['id' => 27, 'parent_id' => 24, 'name' => 'Remote'],
            ['id' => 28, 'parent_id' => 24, 'name' => 'Freelance'],
            ['id' => 29, 'parent_id' => 24, 'name' => 'Probation'],
            
            // Vacation Types Parent
            ['id' => 30, 'parent_id' => null, 'name' => 'Vacation Type'],
            
            // Vacation Types
            ['id' => 31, 'parent_id' => 30, 'name' => 'Vacation'],
            ['id' => 32, 'parent_id' => 30, 'name' => 'Sick Leave'],
            ['id' => 33, 'parent_id' => 30, 'name' => 'Holiday'],
            ['id' => 34, 'parent_id' => 30, 'name' => 'Unpaid'],
        ];

        foreach ($lookupData as $data) {
            // Check if the ID already exists, if not insert the record
            $exists = DB::table('lookup')->where('id', $data['id'])->exists();
            
            if (!$exists) {
                DB::table('lookup')->insert([
                    'id' => $data['id'],
                    'parent_id' => $data['parent_id'],
                    'name' => $data['name'],
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
        // Remove the inserted records in reverse order to handle foreign key constraints
        $idsToRemove = [32, 31, 30, 29, 28, 27, 26, 25, 24, 23, 22, 21, 20, 19, 18, 17, 14, 13, 12, 11, 10, 9, 8, 7, 6, 5, 4, 1];
        
        foreach ($idsToRemove as $id) {
            DB::table('lookup')->where('id', $id)->delete();
        }
    }
};
