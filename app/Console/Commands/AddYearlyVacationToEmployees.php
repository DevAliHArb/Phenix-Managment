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
    protected $signature = 'employees:add-yearly-vacation {--dry-run : Show the addition value and do not update the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add vacation days to yearly_vacations_total for all employees using work_schedule.vacation_days_per_month (falls back to 1.25)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Try to get the addition value from the work_schedules table
        $addValue = DB::table('work_schedules')->value('vacation_days_per_month');

        // Fallback to 1.25 if not found or invalid
        if (!is_numeric($addValue)) {
            $this->warn('work_schedules.vacation_days_per_month not found or invalid; falling back to 1.25');
            $addValue = 1.25;
        }

        // Cast to float and format with dot decimal separator
        $addValue = (float) $addValue;

        if ($this->option('dry-run')) {
            $this->info(sprintf('Dry run: would add %s to yearly_vacations_total for all employees.', number_format($addValue, 2, '.', '')));
            return 0;
        }

        // Perform an arithmetic update: yearly_vacations_total = yearly_vacations_total + <value>
        $amount = number_format($addValue, 2, '.', '');
        $expression = 'yearly_vacations_total + ' . $amount;

        $affected = DB::table('employees')->update([
            'yearly_vacations_total' => DB::raw($expression)
        ]);

        $this->info(sprintf('Yearly vacations updated for %d employees. Added %s to each record.', $affected, $amount));
    }
}
