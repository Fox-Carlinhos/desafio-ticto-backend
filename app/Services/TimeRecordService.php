<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TimeRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class TimeRecordService
{
    /**
     * Record a time punch for the given employee.
     */
    public function recordPunch(User $user): TimeRecord
    {
        if (!$user->employee) {
            throw new \Exception('Usuário não possui perfil de funcionário');
        }

        try {
            $timeRecord = TimeRecord::create([
                'employee_id' => $user->employee->id,
                'recorded_at' => now(),
            ]);

            $this->logTimeRecordCreation($user, $timeRecord);

            return $timeRecord;

        } catch (\Exception $e) {
            throw new \Exception('Erro ao registrar ponto: ' . $e->getMessage());
        }
    }

    /**
     * Get time records for authenticated employee with filters.
     */
    public function getEmployeeTimeRecords(User $user, Request $request): LengthAwarePaginator
    {
        if (!$user->employee) {
            throw new \Exception('Usuário não possui perfil de funcionário');
        }

        $query = TimeRecord::where('employee_id', $user->employee->id)
            ->orderBy('recorded_at', 'desc');

        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();
            $query->betweenDates($startDate, $endDate);
        }

        if ($request->has('date')) {
            $date = Carbon::createFromFormat('Y-m-d', $request->date);
            $query->byDate($date);
        }

        return $query->paginate(20);
    }

    /**
     * Get summary data for authenticated employee.
     */
    public function getEmployeeSummary(User $user): array
    {
        if (!$user->employee) {
            throw new \Exception('Usuário não possui perfil de funcionário');
        }

        $employee = $user->employee;

        $todayRecords = $this->getTodayRecords($employee);

        $monthRecordsCount = $this->getMonthRecordsCount($employee);

        $weekRecords = $this->getWeekRecords($employee);

        return [
            'today' => [
                'date' => today()->format('d/m/Y'),
                'records' => $todayRecords->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'time' => $record->recorded_at->format('H:i:s'),
                    ];
                }),
                'count' => $todayRecords->count(),
            ],
            'this_month' => [
                'period' => Carbon::now()->startOfMonth()->format('d/m/Y') . ' a ' . Carbon::now()->endOfMonth()->format('d/m/Y'),
                'total_records' => $monthRecordsCount,
            ],
            'last_7_days' => [
                'period' => Carbon::now()->subDays(6)->format('d/m/Y') . ' a ' . Carbon::now()->format('d/m/Y'),
                'daily_records' => $weekRecords->map(function ($dayRecords, $date) {
                    return [
                        'date' => Carbon::createFromFormat('Y-m-d', $date)->format('d/m/Y'),
                        'count' => $dayRecords->count(),
                        'times' => $dayRecords->map(function ($record) {
                            return $record->recorded_at->format('H:i:s');
                        })->toArray(),
                    ];
                })->values(),
            ],
        ];
    }

    /**
     * Get today's status for authenticated employee.
     */
    public function getTodayStatus(User $user): array
    {
        if (!$user->employee) {
            throw new \Exception('Usuário não possui perfil de funcionário');
        }

        $todayRecords = $this->getTodayRecords($user->employee);
        $lastRecord = $todayRecords->last();

        return [
            'date' => today()->format('d/m/Y'),
            'records_count' => $todayRecords->count(),
            'last_record' => $lastRecord ? [
                'time' => $lastRecord->recorded_at->format('H:i:s'),
                'formatted' => $lastRecord->formatted_recorded_at,
            ] : null,
            'can_record' => true,
        ];
    }

    /**
     * Get all time records for admin with filters (any employee).
     */
    public function getAdminTimeRecords(Request $request): LengthAwarePaginator
    {
        $query = TimeRecord::with(['employee.user'])
            ->orderBy('recorded_at', 'desc');

        if ($request->start_date) {
            $query->filterByDate($request->start_date, $request->end_date ?? $request->start_date);
        }

        if ($request->employee_id) {
            $query->filterByEmployee($request->employee_id);
        }

        return $query->paginate(20);
    }

    /**
     * Get time records for specific employee (admin access).
     */
    public function getEmployeeRecordsForAdmin(Employee $employee, Request $request): LengthAwarePaginator
    {
        $query = TimeRecord::where('employee_id', $employee->id)
            ->orderBy('recorded_at', 'desc');

        if ($request->start_date) {
            $query->filterByDate($request->start_date, $request->end_date ?? $request->start_date);
        }

        return $query->paginate(20);
    }

    /**
     * Format time record data for API response.
     */
    public function formatTimeRecordData(TimeRecord $record): array
    {
        return [
            'id' => $record->id,
            'recorded_at' => $record->formatted_recorded_at,
            'date' => $record->recorded_at->format('d/m/Y'),
            'time' => $record->recorded_at->format('H:i:s'),
        ];
    }

    /**
     * Format admin time record data with employee info.
     */
    public function formatAdminTimeRecordData(TimeRecord $record): array
    {
        return [
            'id' => $record->id,
            'recorded_at' => $record->formatted_recorded_at,
            'date' => $record->recorded_at->format('d/m/Y'),
            'time' => $record->recorded_at->format('H:i:s'),
            'employee' => [
                'id' => $record->employee->id,
                'full_name' => $record->employee->full_name,
                'position' => $record->employee->position,
                'user' => [
                    'name' => $record->employee->user->name,
                    'email' => $record->employee->user->email,
                ]
            ]
        ];
    }

    /**
     * Get today's records for specific employee.
     */
    private function getTodayRecords(Employee $employee): Collection
    {
        return TimeRecord::where('employee_id', $employee->id)
            ->byDate(today())
            ->orderBy('recorded_at', 'asc')
            ->get();
    }

    /**
     * Get this month's records count for specific employee.
     */
    private function getMonthRecordsCount(Employee $employee): int
    {
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        return TimeRecord::where('employee_id', $employee->id)
            ->betweenDates($monthStart, $monthEnd)
            ->count();
    }

    /**
     * Get last 7 days records grouped by date for specific employee.
     */
    private function getWeekRecords(Employee $employee): Collection
    {
        $weekStart = Carbon::now()->subDays(6)->startOfDay();
        $weekEnd = Carbon::now()->endOfDay();

        return TimeRecord::where('employee_id', $employee->id)
            ->betweenDates($weekStart, $weekEnd)
            ->orderBy('recorded_at', 'desc')
            ->get()
            ->groupBy(function ($record) {
                return $record->recorded_at->format('Y-m-d');
            });
    }

    /**
     * Log time record creation for audit purposes.
     */
    private function logTimeRecordCreation(User $user, TimeRecord $timeRecord): void
    {
        Log::info('Time record created', [
            'time_record_id' => $timeRecord->id,
            'employee_id' => $user->employee->id,
            'employee_name' => $user->employee->full_name,
            'employee_position' => $user->employee->position,
            'manager_id' => $user->employee->manager_id,
            'recorded_at' => $timeRecord->recorded_at->toISOString(),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
