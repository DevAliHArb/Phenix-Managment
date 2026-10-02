<?php

namespace Tests\Feature;

use App\Imports\EmployeeTimeImport;
use App\Models\EmployeeTime;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EmployeeTimeImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'import_test', 'database.connections.import_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('acc_number');
            foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                $table->boolean($day)->default(true);
            }
            $table->softDeletes();
        });
        Schema::create('employee_times', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('acc_number')->nullable();
            $table->date('date');
            foreach (['clock_in', 'clock_out', 'total_time', 'reason', 'vacation_type'] as $column) {
                $table->string($column)->nullable();
            }
            $table->boolean('off_day')->default(false);
            $table->timestamps();
        });
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
        });
        Schema::create('vacation_dates', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name');
        });
        Schema::create('employee_vacations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('date');
            $table->integer('lookup_type_id');
            $table->string('reason')->nullable();
        });
        DB::table('employees')->insert(['id' => 1, 'acc_number' => '100']);
    }

    private function row(string $date, ?string $in = null, ?string $out = null): array
    {
        return [1, '100', 'Employee', '', $date, $in, $out];
    }

    public function test_missing_dates_and_blank_punch_rows_are_unknown(): void
    {
        (new EmployeeTimeImport())->collection(collect([
            $this->row('01/09/2026', '09:00', '17:00'),
            $this->row('04/09/2026'),
        ]));

        $this->assertSame(4, EmployeeTime::count());
        foreach (['2026-09-02', '2026-09-03', '2026-09-04'] as $date) {
            $record = EmployeeTime::where('date', $date)->firstOrFail();
            $this->assertSame('Unknown', $record->vacation_type);
            $this->assertNull($record->clock_in);
            $this->assertNull($record->clock_out);
            $this->assertNull($record->total_time);
            $this->assertFalse((bool) $record->off_day);
        }
    }

    public function test_blank_days_preserve_known_weekends_holidays_and_vacations(): void
    {
        DB::table('employees')->where('id', 1)->update(['saturday' => false]);
        DB::table('vacation_dates')->insert(['date' => '2026-09-02', 'name' => 'Holiday']);
        DB::table('employee_vacations')->insert([
            'employee_id' => 1, 'date' => '2026-09-03', 'lookup_type_id' => 31, 'reason' => 'Leave',
        ]);
        (new EmployeeTimeImport())->collection(collect([
            $this->row('01/09/2026', '09:00', '17:00'),
            $this->row('02/09/2026'),
            $this->row('03/09/2026'),
            $this->row('05/09/2026'),
        ]));

        foreach (['2026-09-02' => 'Holiday', '2026-09-03' => 'Vacation', '2026-09-05' => 'Off'] as $date => $status) {
            $this->assertSame($status, EmployeeTime::where('date', $date)->firstOrFail()->vacation_type);
        }
        $this->assertSame('Unknown', EmployeeTime::where('date', '2026-09-04')->firstOrFail()->vacation_type);
    }

    public function test_reimport_updates_unknown_punches_without_duplicates(): void
    {
        $import = new EmployeeTimeImport();
        $import->collection(collect([$this->row('01/09/2026')]));
        $import->collection(collect([$this->row('01/09/2026', '09:00', '17:00')]));
        $import->collection(collect([$this->row('01/09/2026')]));

        $this->assertSame(1, EmployeeTime::count());
        $record = EmployeeTime::firstOrFail();
        $this->assertSame('Attended', $record->vacation_type);
        $this->assertSame('09:00:00', $record->clock_in);
        $this->assertSame('17:00:00', $record->clock_out);
        $this->assertSame('08:00:00', $record->total_time);
    }
}
