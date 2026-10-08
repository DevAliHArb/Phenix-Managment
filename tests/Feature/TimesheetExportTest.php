<?php

namespace Tests\Feature;

use DOMDocument;
use DOMXPath;
use Tests\TestCase;

class TimesheetExportTest extends TestCase
{
    public function test_exports_only_calculate_extra_time_for_complete_rows(): void
    {
        $timesheet = collect([
            ['date' => '2026-09-01', 'timein' => '09:00', 'timeout' => '19:00', 'totalhours' => '10:00', 'totalhourscalc' => 10],
            ['date' => '2026-09-02', 'timein' => null, 'timeout' => null, 'totalhourscalc' => 0],
            ['date' => '2026-09-03', 'timein' => '09:00', 'timeout' => null, 'totalhourscalc' => 0],
            ['date' => '2026-09-04', 'timein' => null, 'timeout' => '17:00', 'totalhourscalc' => 0],
            ['date' => '2026-09-07', 'timein' => '09:00', 'timeout' => '17:00', 'totalhourscalc' => null],
        ])->map(fn ($row) => $row + ['dayoff' => false, 'is_weekend' => false]);
        $sheet = [
            'employee' => (object) ['name' => 'Employee'],
            'department' => 'Department',
            'month' => 9,
            'year' => 2026,
            'timesheet' => $timesheet,
            'times' => collect(),
            'vacations' => [],
            'sickleave' => [],
            'offdays' => [],
            'unpaid' => [],
            'halfday' => ['2026-09-02'],
            'attendanceRequired' => 22,
        ];

        foreach (['export.timesheet', 'export.timesheet_multiple'] as $view) {
            $html = view($view, $view === 'export.timesheet' ? $sheet : ['sheets' => [$sheet]])->render();
            $document = new DOMDocument();
            @$document->loadHTML($html);
            $xpath = new DOMXPath($document);

            foreach (['02/09/2026', '03/09/2026', '04/09/2026', '07/09/2026'] as $date) {
                $cell = $xpath->query("//tr[td[2][normalize-space(.)='$date']]/td[8]")->item(0);
                $this->assertNotNull($cell);
                $this->assertSame('', trim($cell->textContent), "$view: $date must have no extra time");
            }

            $summary = $xpath->query("//td[contains(., 'Extra Time')]/following-sibling::td[1]")->item(0);
            $this->assertNotNull($summary);
            $this->assertStringContainsString('+1:00', $summary->textContent, "$view: empty and incomplete rows must not reduce the total");
        }
    }
}
