<?php

namespace Tests\Feature;

use App\Http\Controllers\EmployeeTimeController;
use App\Imports\EmployeeTimeImport;
use App\Models\AttendanceCalculationHistory;
use App\Models\Employee;
use App\Models\EmployeeTime;
use App\Models\Lookup;
use App\Models\MachineRecord;
use App\Services\AttendanceGapService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceGapFillingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Use a dedicated in-memory connection; never migrate or change real attendance.
        config([
            'database.default' => 'attendance_gap_test',
            'database.connections.attendance_gap_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        $schema = DB::connection('attendance_gap_test')->getSchemaBuilder();

        $schema->create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('acc_number');
            $table->string('status');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                $table->boolean($day)->default(false);
            }
            $table->softDeletes();
            $table->timestamps();
        });
        $schema->create('machine_records', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id');
            $table->unsignedBigInteger('event_type_id')->nullable();
            $table->dateTime('timestamp');
            $table->boolean('calculated');
            $table->unsignedBigInteger('employee_time_id')->nullable();
            $table->timestamps();
        });
        $schema->create('employee_times', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('acc_number');
            $table->date('date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->string('total_time')->nullable();
            $table->boolean('off_day')->default(false);
            $table->string('reason')->nullable();
            $table->string('vacation_type')->nullable();
            $table->string('total_leave_diff')->nullable();
            $table->string('total_break_diff')->nullable();
            $table->boolean('flagged')->nullable();
            $table->string('break_flag')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'date']);
        });
        $schema->create('attendance_calculation_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->unique();
            $table->date('last_processed_date')->nullable();
            $table->timestamps();
        });
        $schema->create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
        $schema->create('lookup', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
        });
        foreach (['vacation_dates', 'employee_vacations', 'sick_leaves', 'yearly_vacations'] as $name) {
            $schema->create($name, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id')->nullable();
                $table->date('date');
                $table->string('name')->nullable();
                $table->string('reason')->nullable();
                $table->unsignedBigInteger('lookup_type_id')->nullable();
                $table->softDeletes();
                $table->timestamps();
            });
        }

        Carbon::setTestNow('2026-09-08 18:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        DB::purge('attendance_gap_test');
        parent::tearDown();
    }

    public function test_old_unresolved_punch_does_not_block_later_gaps_or_change_attendance(): void
    {
        $employee = Employee::create([
            'acc_number' => '14',
            'status' => 'active',
            'monday' => true,
            'tuesday' => true,
            'wednesday' => true,
            'thursday' => true,
            'friday' => true,
        ]);
        $history = AttendanceCalculationHistory::create([
            'employee_id' => $employee->id,
            'last_processed_date' => '2025-08-24',
        ]);
        $pendingPunch = MachineRecord::create([
            'emp_id' => '14',
            'timestamp' => '2025-08-25 10:03:24',
            'calculated' => false,
        ]);
        $this->createAttendance($employee, '2025-08-27');
        // Calculated punch dates must also remain excluded, even without a saved row.
        MachineRecord::create([
            'emp_id' => '14',
            'timestamp' => '2025-08-28 09:00:00',
            'calculated' => true,
        ]);
        MachineRecord::create([
            'emp_id' => '14',
            'timestamp' => '2026-09-02 09:00:00',
            'calculated' => false,
        ]);
        MachineRecord::create([
            'emp_id' => '14',
            'timestamp' => '2026-09-08 17:00:00',
            'calculated' => true,
        ]);
        $attendance = EmployeeTime::create([
            'employee_id' => $employee->id,
            'acc_number' => '14',
            'date' => '2026-09-07',
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
            'total_time' => '08:00:00',
            'reason' => 'Existing attendance',
        ]);
        $originalAttendance = $attendance->fresh()->getAttributes();
        $originalPunch = $pendingPunch->fresh()->getAttributes();
        $controller = app(EmployeeTimeController::class);

        $this->assertGreaterThan(0, $controller->addVacantRows());

        $workingDay = EmployeeTime::where('employee_id', $employee->id)->where('date', '2026-09-01')->firstOrFail();
        $this->assertSame('Unknown', $workingDay->reason);
        $this->assertNull($workingDay->clock_in);
        $this->assertNull($workingDay->clock_out);
        foreach (['2026-09-05', '2026-09-06'] as $date) {
            $offDay = EmployeeTime::where('employee_id', $employee->id)->where('date', $date)->firstOrFail();
            $this->assertSame('Off', $offDay->vacation_type);
            $this->assertSame('Weekend', $offDay->reason);
        }
        $this->assertFalse(EmployeeTime::where('employee_id', $employee->id)
            ->whereIn('date', ['2025-08-25', '2025-08-26', '2025-08-28', '2026-09-02', '2026-09-08'])->exists());
        $this->assertSame($originalAttendance, $attendance->fresh()->getAttributes());
        $this->assertSame($originalPunch, $pendingPunch->fresh()->getAttributes());
        $this->assertSame('2026-09-07', $history->fresh()->last_processed_date->toDateString());

        $rowCount = EmployeeTime::count();
        $this->assertSame(0, $controller->addVacantRows());
        $this->assertSame($rowCount, EmployeeTime::count());
    }

    public function test_machine_filling_uses_only_each_active_employees_combined_attendance_range(): void
    {
        $employee = $this->createEmployee('14');
        $other = $this->createEmployee('15');
        $inactive = $this->createEmployee('16', ['status' => 'inactive']);
        $withoutAttendance = $this->createEmployee('17');
        $this->createAttendance($employee, '2026-09-07');
        $this->createAttendance($employee, '2026-09-10');
        $this->createAttendance($other, '2025-01-04');
        $this->createAttendance($other, '2025-01-05');
        $this->createAttendance($inactive, '2026-09-01');
        $this->createAttendance($inactive, '2026-09-07');
        // A previous scan must not hide missing rows in the current combined range.
        AttendanceCalculationHistory::create([
            'employee_id' => $employee->id,
            'last_processed_date' => '2026-10-06',
        ]);
        foreach (['2025-01-04 12:00:00', '2026-10-07 12:00:00'] as $timestamp) {
            MachineRecord::create(['emp_id' => '15', 'timestamp' => $timestamp, 'calculated' => true]);
        }
        MachineRecord::create(['emp_id' => '17', 'timestamp' => '2026-09-01 09:00:00', 'calculated' => false]);

        $controller = app(EmployeeTimeController::class);
        $this->assertSame(2, $controller->addVacantRows());
        $this->assertSame(['2026-09-07', '2026-09-08', '2026-09-09', '2026-09-10'],
            EmployeeTime::where('employee_id', $employee->id)->orderBy('date')->pluck('date')->all());
        $this->assertSame(2, EmployeeTime::where('employee_id', $other->id)->count());
        $this->assertSame(2, EmployeeTime::where('employee_id', $inactive->id)->count());
        $this->assertSame(0, EmployeeTime::where('employee_id', $withoutAttendance->id)->count());
        $this->assertFalse(AttendanceCalculationHistory::where('employee_id', $withoutAttendance->id)->exists());
        $this->assertSame(0, $controller->addVacantRows());
    }

    public function test_shared_service_never_fills_gaps_for_inactive_employees(): void
    {
        $employee = $this->createEmployee('14', ['status' => 'inactive']);
        $this->createAttendance($employee, '2026-09-01');
        $this->createAttendance($employee, '2026-09-07');
        $service = app(AttendanceGapService::class);

        $this->assertSame(0, $service->fillBetweenAttendance($employee));
        $this->assertSame(0, $service->fillMissingDays($employee, '2026-09-01', '2026-09-07', true));
        $this->assertSame(2, EmployeeTime::count());
    }

    public function test_import_fills_between_old_and_new_attendance_without_a_month_prefix(): void
    {
        $employee = $this->createEmployee('14');
        $newEmployee = $this->createEmployee('15');
        $this->createAttendance($employee, '2026-09-01');
        $import = new EmployeeTimeImport();

        $import->collection(collect([
            collect([1, '14', 'Employee', '', '03/09/2026', '09:00:00', '17:00:00']),
            collect([2, '15', 'Employee', '', '07/09/2026', '09:00:00', '17:00:00']),
            collect([2, '15', 'Employee', '', '09/09/2026', '09:00:00', '17:00:00']),
        ]));

        $this->assertSame(['2026-09-01', '2026-09-02', '2026-09-03'],
            EmployeeTime::where('employee_id', $employee->id)->orderBy('date')->pluck('date')->all());
        $this->assertSame(['2026-09-07', '2026-09-08', '2026-09-09'],
            EmployeeTime::where('employee_id', $newEmployee->id)->orderBy('date')->pluck('date')->all());
        $this->assertSame('Unknown', EmployeeTime::where('employee_id', $employee->id)
            ->where('date', '2026-09-02')->firstOrFail()->reason);
    }

    public function test_shared_service_respects_employment_dates_and_leave_labels(): void
    {
        $employee = $this->createEmployee('14', ['start_date' => '2026-09-03', 'end_date' => '2026-09-07']);
        $this->createAttendance($employee, '2026-09-01');
        $this->createAttendance($employee, '2026-09-09');
        DB::table('vacation_dates')->insert(['date' => '2026-09-04', 'name' => 'Holiday']);
        DB::table('employee_vacations')->insert([
            'employee_id' => $employee->id,
            'date' => '2026-09-07',
            'lookup_type_id' => 31,
            'reason' => 'Approved leave',
        ]);

        $this->assertSame(5, app(AttendanceGapService::class)->fillBetweenAttendance($employee));
        $this->assertFalse(EmployeeTime::where('employee_id', $employee->id)
            ->whereIn('date', ['2026-09-02', '2026-09-08'])->exists());
        $this->assertSame('Holiday', EmployeeTime::where('date', '2026-09-04')->firstOrFail()->vacation_type);
        $this->assertSame('Vacation', EmployeeTime::where('date', '2026-09-07')->firstOrFail()->vacation_type);
        $this->assertSame('Approved leave', EmployeeTime::where('date', '2026-09-07')->firstOrFail()->reason);
    }

    public function test_machine_calculation_marks_saved_days_attended_and_preserves_existing_leave(): void
    {
        $employee = $this->createEmployee('14');
        $unknown = EmployeeTime::create([
            'employee_id' => $employee->id,
            'acc_number' => '14',
            'date' => '2026-09-03',
            'reason' => 'Unknown',
            'vacation_type' => 'Unknown',
            'off_day' => true,
        ]);
        $leave = EmployeeTime::create([
            'employee_id' => $employee->id,
            'acc_number' => '14',
            'date' => '2026-09-04',
            'reason' => 'Approved leave',
            'vacation_type' => 'Vacation',
            'off_day' => true,
        ]);
        foreach (['2026-09-01', '2026-09-03', '2026-09-04'] as $date) {
            $this->createMachineDay($employee, $date);
        }

        $response = app(EmployeeTimeController::class)->calculateAttendance(
            Request::create('/', 'POST', ['add_no_attendance_rows' => false])
        );
        ob_start();
        try {
            $response->sendContent();
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        $this->assertStringContainsString('event: done', $output);
        $this->assertStringNotContainsString('event: error', $output);
        $this->assertSame('Attended', EmployeeTime::where('employee_id', $employee->id)
            ->where('date', '2026-09-01')->firstOrFail()->vacation_type);
        $this->assertSame('Attended', $unknown->fresh()->vacation_type);
        $this->assertSame(0, $unknown->fresh()->off_day);
        $this->assertNull($unknown->fresh()->reason);
        $this->assertSame('Vacation', $leave->fresh()->vacation_type);
        $this->assertSame('Approved leave', $leave->fresh()->reason);
        $this->assertSame(0, MachineRecord::where('calculated', false)->count());
    }

    public function test_partial_empty_and_conflict_resolution_machine_saves_are_attended(): void
    {
        $employee = $this->createEmployee('14');
        $controller = app(EmployeeTimeController::class);
        $this->createMachineDay($employee, '2026-09-01', ['Clock Out']);
        $this->createMachineDay($employee, '2026-09-02', ['Clock Out']);
        $this->createMachineDay($employee, '2026-09-03');

        $partial = $controller->addRelevantAttendanceInfo(Request::create('/', 'POST', [
            'employee_id' => $employee->id, 'date' => '2026-09-01',
        ]));
        $empty = $controller->addEmptyAttendanceRecord(Request::create('/', 'POST', [
            'employee_id' => $employee->id, 'date' => '2026-09-02',
        ]));
        $resolved = $controller->resolveCalculationConflicts(Request::create('/', 'POST', [
            'choices' => ['employee_id' => $employee->id, 'date' => '2026-09-03'],
        ]));

        foreach ([$partial, $empty, $resolved] as $response) {
            $this->assertNotNull($response);
            $this->assertSame(200, $response->getStatusCode());
            $this->assertTrue($response->getData(true)['success']);
        }
        $this->assertSame(['Attended', 'Attended', 'Attended'],
            EmployeeTime::where('employee_id', $employee->id)->orderBy('date')->pluck('vacation_type')->all());
        $partialAttendance = EmployeeTime::where('date', '2026-09-01')->firstOrFail();
        $this->assertNull($partialAttendance->clock_in);
        $this->assertSame('17:00:00', $partialAttendance->clock_out);
        $this->assertSame(0, MachineRecord::where('calculated', false)->count());
    }

    private function createMachineDay(Employee $employee, string $date, array $types = ['Clock In', 'Clock Out']): void
    {
        $lookups = [];
        foreach (['Clock In', 'Clock Out', 'Break In', 'Break Out'] as $code => $name) {
            $lookups[$name] = Lookup::firstOrCreate(['name' => $name], ['code' => $code]);
        }
        foreach ($types as $type) {
            MachineRecord::create([
                'emp_id' => $employee->acc_number,
                'event_type_id' => $lookups[$type]->id,
                'timestamp' => $date . ($type === 'Clock In' ? ' 09:00:00' : ' 17:00:00'),
                'calculated' => false,
            ]);
        }
    }

    private function createEmployee(string $accountNumber, array $attributes = []): Employee
    {
        return Employee::create($attributes + [
            'acc_number' => $accountNumber,
            'status' => 'active',
            'monday' => true,
            'tuesday' => true,
            'wednesday' => true,
            'thursday' => true,
            'friday' => true,
        ]);
    }

    private function createAttendance(Employee $employee, string $date): EmployeeTime
    {
        return EmployeeTime::create([
            'employee_id' => $employee->id,
            'acc_number' => $employee->acc_number,
            'date' => $date,
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
            'total_time' => '08:00:00',
        ]);
    }
}
