<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\TimeRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class TimeRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_time_record_can_be_created_with_valid_data(): void
    {
        $employee = Employee::factory()->create();
        $recordedAt = Carbon::now();

        $timeRecordData = [
            'employee_id' => $employee->id,
            'recorded_at' => $recordedAt,
        ];

        $timeRecord = TimeRecord::create($timeRecordData);

        $this->assertInstanceOf(TimeRecord::class, $timeRecord);
        $this->assertEquals($employee->id, $timeRecord->employee_id);
        $this->assertEquals($recordedAt->format('Y-m-d H:i:s'), $timeRecord->recorded_at->format('Y-m-d H:i:s'));
    }

    public function test_recorded_at_is_cast_to_carbon_datetime(): void
    {
        $timeRecord = TimeRecord::factory()->create(['recorded_at' => '2024-01-15 14:30:45']);

        $this->assertInstanceOf(Carbon::class, $timeRecord->recorded_at);
        $this->assertEquals('2024-01-15 14:30:45', $timeRecord->recorded_at->format('Y-m-d H:i:s'));
    }

    public function test_time_record_belongs_to_employee(): void
    {
        $employee = Employee::factory()->create();
        $timeRecord = TimeRecord::factory()->for($employee)->create();

        $this->assertInstanceOf(Employee::class, $timeRecord->employee);
        $this->assertEquals($employee->id, $timeRecord->employee->id);
    }

    public function test_between_dates_scope_filters_by_date_range(): void
    {
        $startDate = Carbon::parse('2024-01-01 00:00:00');
        $endDate = Carbon::parse('2024-01-31 23:59:59');

        $recordInRange = TimeRecord::factory()->create(['recorded_at' => '2024-01-15 12:00:00']);
        $recordBeforeRange = TimeRecord::factory()->create(['recorded_at' => '2023-12-31 12:00:00']);
        $recordAfterRange = TimeRecord::factory()->create(['recorded_at' => '2024-02-01 12:00:00']);

        $filteredRecords = TimeRecord::betweenDates($startDate, $endDate)->get();

        $this->assertCount(1, $filteredRecords);
        $this->assertTrue($filteredRecords->contains($recordInRange));
        $this->assertFalse($filteredRecords->contains($recordBeforeRange));
        $this->assertFalse($filteredRecords->contains($recordAfterRange));
    }

    public function test_by_date_scope_filters_by_specific_date(): void
    {
        $targetDate = Carbon::parse('2024-01-15');

        $recordOnDate = TimeRecord::factory()->create(['recorded_at' => '2024-01-15 14:30:00']);
        $recordOnDateDifferentTime = TimeRecord::factory()->create(['recorded_at' => '2024-01-15 09:15:00']);
        $recordOnDifferentDate = TimeRecord::factory()->create(['recorded_at' => '2024-01-16 14:30:00']);

        $filteredRecords = TimeRecord::byDate($targetDate)->get();

        $this->assertCount(2, $filteredRecords);
        $this->assertTrue($filteredRecords->contains($recordOnDate));
        $this->assertTrue($filteredRecords->contains($recordOnDateDifferentTime));
        $this->assertFalse($filteredRecords->contains($recordOnDifferentDate));
    }

    public function test_by_employee_scope_filters_by_employee(): void
    {
        $employee1 = Employee::factory()->create();
        $employee2 = Employee::factory()->create();

        $record1 = TimeRecord::factory()->for($employee1)->create();
        $record2 = TimeRecord::factory()->for($employee1)->create();
        $record3 = TimeRecord::factory()->for($employee2)->create();

        $employee1Records = TimeRecord::byEmployee($employee1->id)->get();

        $this->assertCount(2, $employee1Records);
        $this->assertTrue($employee1Records->contains($record1));
        $this->assertTrue($employee1Records->contains($record2));
        $this->assertFalse($employee1Records->contains($record3));
    }

    public function test_formatted_recorded_at_attribute_formats_correctly(): void
    {
        $timeRecord = TimeRecord::factory()->create(['recorded_at' => '2024-01-15 14:30:45']);

        $this->assertEquals('15/01/2024 14:30:45', $timeRecord->formatted_recorded_at);
    }

    public function test_fillable_attributes_are_correct(): void
    {
        $timeRecord = new TimeRecord();
        $expectedFillable = [
            'employee_id',
            'recorded_at',
        ];

        $this->assertEquals($expectedFillable, $timeRecord->getFillable());
    }

    public function test_time_record_factory_creates_valid_record(): void
    {
        $timeRecord = TimeRecord::factory()->create();

        $this->assertNotNull($timeRecord->employee_id);
        $this->assertInstanceOf(Carbon::class, $timeRecord->recorded_at);
        $this->assertInstanceOf(Employee::class, $timeRecord->employee);
    }

    public function test_multiple_records_can_be_created_for_same_employee(): void
    {
        $employee = Employee::factory()->create();

        $record1 = TimeRecord::factory()->for($employee)->create(['recorded_at' => '2024-01-15 08:00:00']);
        $record2 = TimeRecord::factory()->for($employee)->create(['recorded_at' => '2024-01-15 12:00:00']);
        $record3 = TimeRecord::factory()->for($employee)->create(['recorded_at' => '2024-01-15 18:00:00']);

        $employeeRecords = TimeRecord::byEmployee($employee->id)->get();

        $this->assertCount(3, $employeeRecords);
    }

    public function test_scopes_can_be_chained(): void
    {
        $employee = Employee::factory()->create();
        $targetDate = Carbon::parse('2024-01-15');

        $matchingRecord = TimeRecord::factory()->for($employee)->create(['recorded_at' => '2024-01-15 14:30:00']);
        $wrongEmployee = TimeRecord::factory()->create(['recorded_at' => '2024-01-15 14:30:00']);
        $wrongDate = TimeRecord::factory()->for($employee)->create(['recorded_at' => '2024-01-16 14:30:00']);

        $filteredRecords = TimeRecord::byEmployee($employee->id)
            ->byDate($targetDate)
            ->get();

        $this->assertCount(1, $filteredRecords);
        $this->assertTrue($filteredRecords->contains($matchingRecord));
        $this->assertFalse($filteredRecords->contains($wrongEmployee));
        $this->assertFalse($filteredRecords->contains($wrongDate));
    }

    public function test_time_record_can_be_created_with_current_timestamp(): void
    {
        $employee = Employee::factory()->create();
        $now = Carbon::now();

        $timeRecord = TimeRecord::create([
            'employee_id' => $employee->id,
            'recorded_at' => $now,
        ]);

        $this->assertEquals($now->format('Y-m-d H:i:s'), $timeRecord->recorded_at->format('Y-m-d H:i:s'));
    }

    public function test_time_records_are_ordered_by_recorded_at_in_queries(): void
    {
        $employee = Employee::factory()->create();

        $record1 = TimeRecord::factory()->for($employee)->create(['recorded_at' => '2024-01-15 10:00:00']);
        $record2 = TimeRecord::factory()->for($employee)->create(['recorded_at' => '2024-01-15 08:00:00']);
        $record3 = TimeRecord::factory()->for($employee)->create(['recorded_at' => '2024-01-15 12:00:00']);

        $orderedRecords = TimeRecord::byEmployee($employee->id)
            ->orderBy('recorded_at', 'asc')
            ->get();

        $this->assertEquals($record2->id, $orderedRecords->first()->id);
        $this->assertEquals($record3->id, $orderedRecords->last()->id);
    }
}
