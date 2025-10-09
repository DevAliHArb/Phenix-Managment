<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\VacationDate;
use App\Models\Employee;
use App\Models\EmployeeVacation;
use Illuminate\Support\Facades\DB;

class ApplyVacationDatesToEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vacationdates:apply {--date= : Optional date (Y-m-d) to apply; defaults to today} {--dry-run : Show what would be inserted but do not modify DB}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply records from vacation_dates for a given date to all active employees (creates employee_vacations with lookup_type_id = 33)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateOption = $this->option('date');
        $date = $dateOption ? Carbon::parse($dateOption)->toDateString() : Carbon::today()->toDateString();

        $this->info("Checking vacation_dates for date: $date");

        $vacationDates = VacationDate::whereDate('date', $date)->get();

        if ($vacationDates->isEmpty()) {
            $this->info('No vacation_dates found for this date.');
            return 0;
        }

        // Define what we consider an "active" employee.
        // Assumption: active = status = 'active' OR end_date is null. We'll prefer status='active' when available.
        $employees = Employee::where(function ($q) {
            $q->where('status', 'active')->whereNull('end_date');
        })->get();

        if ($employees->isEmpty()) {
            $this->info('No active employees found to apply vacations for.');
            return 0;
        }

        $created = 0;
        $skipped = 0;
        foreach ($vacationDates as $vacDate) {
            foreach ($employees as $employee) {
                $exists = EmployeeVacation::where('employee_id', $employee->id)
                    ->whereDate('date', $vacDate->date)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $payload = [
                    'employee_id' => $employee->id,
                    'reason' => $vacDate->name ?? ('Vacation ' . $vacDate->date),
                    'date' => $vacDate->date,
                    'lookup_type_id' => 33,
                ];

                if ($this->option('dry-run')) {
                    $this->line('[DRY] Would insert: ' . json_encode($payload));
                } else {
                    EmployeeVacation::create($payload);
                }

                $created++;
            }
        }

        $this->info(sprintf('%d employee_vacations created, %d skipped (already existed).', $created, $skipped));

        return 0;
    }
}
