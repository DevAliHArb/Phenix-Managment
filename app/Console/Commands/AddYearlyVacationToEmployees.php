<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddYearlyVacationToEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:add-yearly-vacation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add 1.25 to yearly_vacations_total for all employees';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::table('employees')->update([
            'yearly_vacations_total' => DB::raw('yearly_vacations_total + 1.25')
        ]);
        $this->info('Yearly vacations updated for all employees.');
    }
}
