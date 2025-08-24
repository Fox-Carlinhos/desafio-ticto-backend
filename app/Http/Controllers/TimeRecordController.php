<?php

namespace App\Http\Controllers;

use App\Services\TimeRecordService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Employee;

class TimeRecordController extends Controller
{
    public function __construct(
        private TimeRecordService $timeRecordService
    ) {}
    /**
     * Record time punch for authenticated employee
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $timeRecord = $this->timeRecordService->recordPunch($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Ponto registrado com sucesso',
                'data' => [
                    'id' => $timeRecord->id,
                    'recorded_at' => $timeRecord->formatted_recorded_at,
                    'employee' => [
                        'id' => $request->user()->employee->id,
                        'full_name' => $request->user()->employee->full_name,
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getMessage() === 'Usuário não possui perfil de funcionário' ? 400 : 500);
        }
    }

    /**
     * Get time records for authenticated employee
     */
    public function index(Request $request): JsonResponse
    {
        try {
            /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $timeRecords */
            $timeRecords = $this->timeRecordService->getEmployeeTimeRecords($request->user(), $request);

            return response()->json([
                'success' => true,
                'data' => collect($timeRecords->items())->map(function ($record) {
                    return $this->timeRecordService->formatTimeRecordData($record);
                }),
                'pagination' => [
                    'current_page' => $timeRecords->currentPage(),
                    'last_page' => $timeRecords->lastPage(),
                    'per_page' => $timeRecords->perPage(),
                    'total' => $timeRecords->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get time records summary for authenticated employee
     */
    public function summary(Request $request): JsonResponse
    {
        try {
            $summaryData = $this->timeRecordService->getEmployeeSummary($request->user());

            return response()->json([
                'success' => true,
                'data' => $summaryData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Admin access to all time records
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
            'employee_id' => 'nullable|integer|exists:employees,id',
            'page' => 'nullable|integer|min:1',
        ]);

        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $timeRecords */
        $timeRecords = $this->timeRecordService->getAdminTimeRecords($request);

        $formattedRecords = collect($timeRecords->items())->map(function ($record) {
            return $this->timeRecordService->formatAdminTimeRecordData($record);
        });

        return response()->json([
            'success' => true,
            'data' => $formattedRecords,
            'pagination' => [
                'current_page' => $timeRecords->currentPage(),
                'last_page' => $timeRecords->lastPage(),
                'per_page' => $timeRecords->perPage(),
                'total' => $timeRecords->total(),
            ]
        ]);
    }

    /**
     * Admin access to specific employee's time records
     */
    public function adminEmployeeRecords(Request $request, Employee $employee): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:start_date',
            'page' => 'nullable|integer|min:1',
        ]);

        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $timeRecords */
        $timeRecords = $this->timeRecordService->getEmployeeRecordsForAdmin($employee, $request);

        $formattedRecords = collect($timeRecords->items())->map(function ($record) {
            return $this->timeRecordService->formatTimeRecordData($record);
        });

        return response()->json([
            'success' => true,
            'employee' => [
                'id' => $employee->id,
                'full_name' => $employee->full_name,
                'position' => $employee->position,
                'user' => [
                    'name' => $employee->user->name,
                    'email' => $employee->user->email,
                ]
            ],
            'data' => $formattedRecords,
            'pagination' => [
                'current_page' => $timeRecords->currentPage(),
                'last_page' => $timeRecords->lastPage(),
                'per_page' => $timeRecords->perPage(),
                'total' => $timeRecords->total(),
            ]
        ]);
    }

    /**
     * Get today's status for quick check
     */
    public function todayStatus(Request $request): JsonResponse
    {
        try {
            $statusData = $this->timeRecordService->getTodayStatus($request->user());

            return response()->json([
                'success' => true,
                'data' => $statusData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
